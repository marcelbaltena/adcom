<?php

namespace App\Http\Controllers;

use App\Models\ProjectMilestone;
use App\Models\ProjectTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectMilestoneController extends Controller
{
    /**
     * Store a new project milestone
     */
    public function store(Request $request)
    {
        Log::info('Project Milestone Store Request:', $request->all());
        
        $validated = $request->validate([
            'project_template_id' => 'required|exists:project_templates,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'days_from_start' => 'required|integer|min:0',
            'duration_days' => 'required|integer|min:1',
            'fee_type' => 'required|in:in_fee,extended',
            'pricing_type' => 'required|in:fixed_price,hourly_rate',
            'estimated_hours' => 'nullable|numeric|min:0',
            'deliverables' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $projectTemplate = ProjectTemplate::findOrFail($validated['project_template_id']);
            
            // Get max order
            $maxOrder = $projectTemplate->milestones()->max('order') ?? -1;
            $validated['order'] = $maxOrder + 1;
            
            // Process deliverables
            if (isset($validated['deliverables'])) {
                $validated['deliverables'] = array_filter(array_map('trim', explode("\n", $validated['deliverables'])));
            }
            
            // Update total days if needed
            $totalDays = $validated['days_from_start'] + $validated['duration_days'];
            if ($totalDays > $projectTemplate->total_days) {
                $projectTemplate->update(['total_days' => $totalDays]);
            }
            
            $milestone = ProjectMilestone::create($validated);
            
            DB::commit();
            
            return redirect()->route('project-templates.milestones', $projectTemplate)
                ->with('success', 'Milestone toegevoegd aan project sjabloon');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating project milestone: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update a project milestone
     */
    public function update(Request $request, ProjectMilestone $projectMilestone)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'days_from_start' => 'required|integer|min:0',
            'duration_days' => 'required|integer|min:1',
            'fee_type' => 'required|in:in_fee,extended',
            'pricing_type' => 'required|in:fixed_price,hourly_rate',
            'estimated_hours' => 'nullable|numeric|min:0',
            'deliverables' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Process deliverables
            if (isset($validated['deliverables'])) {
                $validated['deliverables'] = array_filter(array_map('trim', explode("\n", $validated['deliverables'])));
            }
            
            // Update total days if needed
            $totalDays = $validated['days_from_start'] + $validated['duration_days'];
            $projectTemplate = $projectMilestone->projectTemplate;
            if ($totalDays > $projectTemplate->total_days) {
                $projectTemplate->update(['total_days' => $totalDays]);
            }
            
            $projectMilestone->update($validated);
            
            DB::commit();
            
            return redirect()->route('project-templates.milestones', $projectMilestone->projectTemplate)
                ->with('success', 'Milestone bijgewerkt');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete a project milestone
     */
    public function destroy(ProjectMilestone $projectMilestone)
    {
        $projectTemplate = $projectMilestone->projectTemplate;
        
        try {
            $projectMilestone->delete();
            
            // Recalculate total days
            $this->recalculateTotalDays($projectTemplate);
            
            return redirect()->route('project-templates.milestones', $projectTemplate)
                ->with('success', 'Milestone verwijderd');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage());
        }
    }

    /**
     * Reorder project milestones
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'milestone_ids' => 'required|array',
            'milestone_ids.*' => 'exists:project_milestones,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['milestone_ids'] as $index => $milestoneId) {
                ProjectMilestone::where('id', $milestoneId)->update(['order' => $index]);
            }
            
            DB::commit();
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Recalculate total days for project template
     */
    private function recalculateTotalDays(ProjectTemplate $projectTemplate)
    {
        $maxDays = $projectTemplate->milestones()
            ->selectRaw('MAX(days_from_start + duration_days) as max_days')
            ->value('max_days') ?? 0;
            
        $projectTemplate->update(['total_days' => $maxDays]);
    }
}