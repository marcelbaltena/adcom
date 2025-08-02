<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MilestoneController extends Controller
{
    /**
     * Store a newly created milestone
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'fee_type' => 'required|in:in_fee,extended_fee',
            'pricing_type' => 'required|in:fixed_price,hourly_rate',
            'budget' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|numeric|min:0',
            'deliverables' => 'nullable|string',
            'actual_cost' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Debug logging
            Log::info('Milestone data received:', $validated);

            $project = Project::findOrFail($validated['project_id']);
            
            // Handle pricing
            if ($validated['pricing_type'] === 'hourly_rate') {
                $validated['budget'] = ($validated['hourly_rate'] ?? 0) * ($validated['estimated_hours'] ?? 0);
            }
            
            // Map budget to price field for compatibility
            if (isset($validated['budget'])) {
                $validated['price'] = $validated['budget'];
                $validated['budget_amount'] = $validated['budget'];
            }

            // Process deliverables
            if (isset($validated['deliverables'])) {
                $deliverables = array_filter(array_map('trim', explode("\n", $validated['deliverables'])));
                $validated['deliverables'] = json_encode($deliverables);
            }
            
            // Get the highest order number
            $maxOrder = $project->milestones()->max('order') ?? -1;
            $validated['order'] = $maxOrder + 1;
            $validated['position'] = $maxOrder + 1;
            
            // Set defaults - IMPORTANT: ensure actual_hours is not null
            $validated['status'] = 'concept';
            $validated['priority'] = 'normaal';
            $validated['completion_percentage'] = 0;
            $validated['actual_hours'] = $validated['actual_hours'] ?? 0;
            $validated['billable_hours'] = 0;
            $validated['spent'] = $validated['actual_cost'] ?? 0;
            $validated['allocated_budget'] = 0;
            $validated['remaining_budget'] = ($validated['budget'] ?? 0) - ($validated['actual_cost'] ?? 0);
            $validated['budget_status'] = 'on_track';
            $validated['timeline_status'] = 'not_started';
            $validated['manual_progress'] = 0;
            $validated['allow_comments'] = true;
            $validated['notify_watchers'] = true;
            
            // Also set due_date for backwards compatibility
            if (isset($validated['end_date'])) {
                $validated['due_date'] = $validated['end_date'];
            }
            
            $milestone = Milestone::create($validated);
            
            // Update project budget calculations
            if ($project) {
                $project->updateBudgetCalculations();
            }
            
            DB::commit();

            return redirect()
                ->route('projects.milestones', $project)
                ->with('success', 'Milestone succesvol aangemaakt');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create milestone: ' . $e->getMessage());
            Log::error('Data: ' . json_encode($request->all()));
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified milestone
     */
    public function update(Request $request, Milestone $milestone)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|string',
            'priority' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'fee_type' => 'required|in:in_fee,extended_fee',
            'pricing_type' => 'required|in:fixed_price,hourly_rate',
            'budget' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|numeric|min:0',
            'deliverables' => 'nullable|string',
            'actual_cost' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0'
        ]);

        try {
            DB::beginTransaction();

            // Handle pricing
            if ($validated['pricing_type'] === 'hourly_rate') {
                $validated['budget'] = ($validated['hourly_rate'] ?? 0) * ($validated['estimated_hours'] ?? 0);
            }
            
            // Map budget to price field for compatibility
            if (isset($validated['budget'])) {
                $validated['price'] = $validated['budget'];
                $validated['budget_amount'] = $validated['budget'];
            }

            // Process deliverables
            if (isset($validated['deliverables'])) {
                $deliverables = array_filter(array_map('trim', explode("\n", $validated['deliverables'])));
                $validated['deliverables'] = json_encode($deliverables);
            }

            // Ensure actual_hours is not null
            if (isset($validated['actual_hours']) && $validated['actual_hours'] === null) {
                $validated['actual_hours'] = 0;
            }

            // Update spent amount
            if (isset($validated['actual_cost'])) {
                $validated['spent'] = $validated['actual_cost'];
            }

            // Update remaining budget
            $budget = $validated['budget'] ?? $milestone->budget ?? 0;
            $spent = $validated['actual_cost'] ?? $milestone->spent ?? 0;
            $validated['remaining_budget'] = $budget - $spent;

            // Also set due_date for backwards compatibility
            if (isset($validated['end_date'])) {
                $validated['due_date'] = $validated['end_date'];
            }

            // Handle status changes
            if (isset($validated['status'])) {
                if ($validated['status'] === 'completed' && $milestone->status !== 'completed') {
                    $validated['completed_at'] = now();
                    $validated['completion_percentage'] = 100;
                } elseif ($validated['status'] !== 'completed' && $milestone->status === 'completed') {
                    $validated['completed_at'] = null;
                }
            }

            $milestone->update($validated);
            
            // Recalculate budget allocations
            $milestone->updateBudgetCalculations();
            
            // Update project calculations
            if ($milestone->project) {
                $milestone->project->updateBudgetCalculations();
            }
            
            DB::commit();

            return redirect()
                ->route('projects.milestones', $milestone->project)
                ->with('success', 'Milestone succesvol bijgewerkt');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update milestone: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified milestone
     */
    public function destroy(Milestone $milestone)
    {
        try {
            DB::beginTransaction();

            $project = $milestone->project;
            
            // Check if milestone has tasks
            if ($milestone->tasks()->count() > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Kan milestone met taken niet verwijderen.');
            }

            $milestone->delete();
            
            // Update project budget calculations
            if ($project) {
                $project->updateBudgetCalculations();
            }
            
            DB::commit();

            return redirect()
                ->route('projects.milestones', $project)
                ->with('success', 'Milestone succesvol verwijderd');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to delete milestone: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Kon milestone niet verwijderen.');
        }
    }

    /**
     * Reorder milestones (for drag & drop)
     */
    public function reorder(Request $request)
    {
        try {
            $validated = $request->validate([
                'milestone_ids' => 'required|array',
                'milestone_ids.*' => 'exists:milestones,id'
            ]);

            DB::beginTransaction();
            
            // Update the order
            foreach ($validated['milestone_ids'] as $index => $milestoneId) {
                Milestone::where('id', $milestoneId)->update([
                    'order' => $index,
                    'position' => $index
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Milestones succesvol geherordend'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to reorder milestones: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Kon milestones niet herordenen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get milestone budget details for AJAX
     */
    public function getBudgetDetails(Milestone $milestone)
    {
        try {
            $budgetInfo = [
                'budget' => $milestone->budget ?? 0,
                'allocated_budget' => $milestone->allocated_budget ?? 0,
                'spent' => $milestone->spent ?? 0,
                'remaining_budget' => $milestone->remaining_budget ?? 0,
                'budget_status' => $milestone->budget_status,
                'tasks_count' => $milestone->tasks()->count(),
                'completed_tasks' => $milestone->tasks()->where('status', 'completed')->count(),
                'completion_percentage' => $milestone->completion_percentage ?? 0
            ];

            return response()->json([
                'success' => true,
                'data' => $budgetInfo
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get budget details: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Kon budget details niet ophalen'
            ], 500);
        }
    }

    /**
     * Update milestone budget via AJAX
     */
    public function updateBudget(Request $request, Milestone $milestone)
    {
        try {
            $validated = $request->validate([
                'budget' => 'required|numeric|min:0'
            ]);

            DB::beginTransaction();

            // Update budget fields
            $milestone->update([
                'budget' => $validated['budget'],
                'price' => $validated['budget'],
                'budget_amount' => $validated['budget']
            ]);

            // Recalculate all budget fields
            $milestone->updateBudgetCalculations();
            
            // Update project calculations
            if ($milestone->project) {
                $milestone->project->updateBudgetCalculations();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Budget succesvol bijgewerkt',
                'data' => [
                    'budget' => $milestone->budget,
                    'allocated_budget' => $milestone->allocated_budget,
                    'remaining_budget' => $milestone->remaining_budget,
                    'budget_status' => $milestone->budget_status
                ]
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update budget: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Kon budget niet bijwerken: ' . $e->getMessage()
            ], 500);
        }
    }
}