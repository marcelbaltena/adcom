<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\Milestone;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TaskController extends Controller
{
    /**
     * Store a newly created task
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'milestone_id' => 'required|exists:milestones,id',
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'priority' => 'nullable|string',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'fee_type' => 'required|in:in_fee,extended_fee',
            'pricing_type' => 'required|in:fixed_price,hourly_rate',
            'budget' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|numeric|min:0',
            'status' => 'nullable|string',
            'checklist' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Map priority values
            $priorityMap = [
                'low' => 'laag',
                'medium' => 'normaal', 
                'high' => 'hoog',
                'laag' => 'laag',
                'normaal' => 'normaal',
                'hoog' => 'hoog'
            ];
            
            $validated['priority'] = $priorityMap[$validated['priority'] ?? 'normaal'] ?? 'normaal';

            // Get milestone and inherit dates if not provided
            $milestone = Milestone::findOrFail($validated['milestone_id']);
            
            if (empty($validated['start_date']) && $milestone->start_date) {
                $validated['start_date'] = $milestone->start_date;
            }
            if (empty($validated['end_date']) && $milestone->end_date) {
                $validated['end_date'] = $milestone->end_date;
            }

            // Handle pricing
            if ($validated['pricing_type'] === 'hourly_rate') {
                $validated['budget'] = ($validated['hourly_rate'] ?? 0) * ($validated['estimated_hours'] ?? 0);
            }
            
            // Map budget to price field if your database uses 'price'
            if (isset($validated['budget'])) {
                $validated['price'] = $validated['budget'];
                $validated['budget_amount'] = $validated['budget'];
            }

            // Process checklist
            if (isset($validated['checklist'])) {
                $checklist = array_filter(array_map('trim', explode("\n", $validated['checklist'])));
                $validated['checklist'] = json_encode($checklist);
            }

            // Get the highest order number
            $maxOrder = $milestone->tasks()->max('order') ?? -1;
            $validated['order'] = $maxOrder + 1;
            $validated['position'] = $maxOrder + 1;

            // Set defaults
            $validated['status'] = $validated['status'] ?? 'concept';
            $validated['completion_percentage'] = 0;
            $validated['spent_amount'] = 0;
            $validated['remaining_budget'] = $validated['budget'] ?? 0;
            
            // Create task
            $task = Task::create($validated);

            // Update milestone calculations
            $milestone->updateBudgetCalculations();

            DB::commit();

            return redirect()
                ->route('projects.milestones', $task->project)
                ->with('success', 'Taak succesvol aangemaakt');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create task: ' . $e->getMessage());
            Log::error('Data: ' . json_encode($request->all()));
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified task
     */
    public function update(Request $request, Task $task)
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
            'actual_cost' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0',
            'checklist' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Map priority values
            $priorityMap = [
                'low' => 'laag',
                'medium' => 'normaal',
                'high' => 'hoog',
                'laag' => 'laag',
                'normaal' => 'normaal',
                'hoog' => 'hoog'
            ];
            
            if (isset($validated['priority'])) {
                $validated['priority'] = $priorityMap[$validated['priority']] ?? $validated['priority'];
            }

            // Handle pricing
            if ($validated['pricing_type'] === 'hourly_rate') {
                $validated['budget'] = ($validated['hourly_rate'] ?? 0) * ($validated['estimated_hours'] ?? 0);
            }
            
            // Map budget to price field if needed
            if (isset($validated['budget'])) {
                $validated['price'] = $validated['budget'];
                $validated['budget_amount'] = $validated['budget'];
            }

            // Process checklist
            if (isset($validated['checklist'])) {
                $checklist = array_filter(array_map('trim', explode("\n", $validated['checklist'])));
                $validated['checklist'] = json_encode($checklist);
            }

            // Handle status changes
            if (isset($validated['status'])) {
                if ($validated['status'] === 'voltooid' && $task->status !== 'voltooid') {
                    $validated['completed_at'] = now();
                    $validated['completion_percentage'] = 100;
                } elseif ($validated['status'] !== 'voltooid' && $task->status === 'voltooid') {
                    $validated['completed_at'] = null;
                }
            }

            // Update remaining budget
            if (isset($validated['actual_cost'])) {
                $budget = $validated['budget'] ?? $task->budget ?? 0;
                $validated['remaining_budget'] = $budget - $validated['actual_cost'];
                $validated['spent_amount'] = $validated['actual_cost'];
            }

            $task->update($validated);

            // Update milestone calculations
            if ($task->milestone) {
                $task->milestone->updateBudgetCalculations();
            }

            DB::commit();

            return redirect()
                ->route('projects.milestones', $task->project)
                ->with('success', 'Taak succesvol bijgewerkt');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update task: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified task
     */
    public function destroy(Task $task)
    {
        try {
            DB::beginTransaction();

            $project = $task->project;
            $milestone = $task->milestone;

            // Check if task has subtasks
            if ($task->subtasks()->count() > 0) {
                return redirect()
                    ->back()
                    ->with('error', 'Kan taak met subtaken niet verwijderen.');
            }

            $task->delete();

            // Update milestone budget calculations
            if ($milestone) {
                $milestone->updateBudgetCalculations();
            }

            DB::commit();

            return redirect()
                ->route('projects.milestones', $project)
                ->with('success', 'Taak succesvol verwijderd');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to delete task: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Kon taak niet verwijderen.');
        }
    }

    /**
     * Reorder tasks within a milestone (for drag & drop)
     */
    public function reorder(Request $request)
    {
        try {
            $validated = $request->validate([
                'task_ids' => 'required|array',
                'task_ids.*' => 'exists:tasks,id'
            ]);

            DB::beginTransaction();

            // Update the order
            foreach ($validated['task_ids'] as $index => $taskId) {
                Task::where('id', $taskId)->update([
                    'order' => $index,
                    'position' => $index
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Taken succesvol geherordend'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to reorder tasks: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Kon taken niet herordenen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move a task to a different milestone (for drag & drop)
     */
    public function move(Request $request, Task $task)
    {
        try {
            $validated = $request->validate([
                'milestone_id' => 'required|exists:milestones,id'
            ]);

            DB::beginTransaction();

            $newMilestoneId = $validated['milestone_id'];
            $oldMilestoneId = $task->milestone_id;

            // Verify the new milestone belongs to the same project
            $newMilestone = Milestone::findOrFail($newMilestoneId);
            if ($newMilestone->project_id !== $task->project_id) {
                throw new \Exception('Kan taak niet verplaatsen naar milestone in ander project');
            }

            // Move the task
            $task->milestone_id = $newMilestoneId;
            
            // Get the highest order number in the new milestone
            $maxOrder = Task::where('milestone_id', $newMilestoneId)->max('order') ?? -1;
            $task->order = $maxOrder + 1;
            $task->position = $maxOrder + 1;
            
            $task->save();

            // Update old milestone budget
            $oldMilestone = Milestone::find($oldMilestoneId);
            if ($oldMilestone) {
                $oldMilestone->updateBudgetCalculations();
            }

            // Update new milestone budget
            $newMilestone->updateBudgetCalculations();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Taak succesvol verplaatst'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to move task: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Kon taak niet verplaatsen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get task budget information for AJAX
     */
    public function getBudget(Task $task)
    {
        try {
            return response()->json([
                'success' => true,
                'data' => [
                    'budget' => $task->budget ?? $task->price ?? 0,
                    'spent' => $task->spent_amount ?? $task->actual_cost ?? 0,
                    'remaining' => $task->remaining_budget ?? 0,
                    'percentage' => $task->completion_percentage ?? 0
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to get budget information: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Kon budget informatie niet ophalen'
            ], 500);
        }
    }
}