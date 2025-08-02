<?php

namespace App\Http\Controllers;

use App\Models\ProjectTask;
use App\Models\ProjectMilestone;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectTaskController extends Controller
{
    /**
     * Store a new project task
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_milestone_id' => 'required|exists:project_milestones,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fee_type' => 'required|in:in_fee,extended',
            'pricing_type' => 'required|in:fixed_price,hourly_rate',
            'estimated_hours' => 'nullable|numeric|min:0',
            'deliverables' => 'nullable|string',
            'checklist_items' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $milestone = ProjectMilestone::findOrFail($validated['project_milestone_id']);
            
            // Get max order
            $maxOrder = $milestone->tasks()->max('order') ?? -1;
            $validated['order'] = $maxOrder + 1;
            
            // Process deliverables
            if (isset($validated['deliverables'])) {
                $validated['deliverables'] = array_filter(array_map('trim', explode("\n", $validated['deliverables'])));
            }
            
            // Process checklist items
            if (isset($validated['checklist_items'])) {
                $validated['checklist_items'] = array_filter(array_map('trim', explode("\n", $validated['checklist_items'])));
            }
            
            $task = ProjectTask::create($validated);
            
            DB::commit();
            
            return redirect()->route('project-templates.milestones', $milestone->projectTemplate)
                ->with('success', 'Taak toegevoegd')
                ->with('open_milestone', $milestone->id);
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating project task: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update a project task
     */
    public function update(Request $request, ProjectTask $projectTask)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fee_type' => 'required|in:in_fee,extended',
            'pricing_type' => 'required|in:fixed_price,hourly_rate',
            'estimated_hours' => 'nullable|numeric|min:0',
            'deliverables' => 'nullable|string',
            'checklist_items' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Process deliverables
            if (isset($validated['deliverables'])) {
                $validated['deliverables'] = array_filter(array_map('trim', explode("\n", $validated['deliverables'])));
            }
            
            // Process checklist items
            if (isset($validated['checklist_items'])) {
                $validated['checklist_items'] = array_filter(array_map('trim', explode("\n", $validated['checklist_items'])));
            }
            
            $projectTask->update($validated);
            
            DB::commit();
            
            return redirect()->route('project-templates.milestones', $projectTask->milestone->projectTemplate)
                ->with('success', 'Taak bijgewerkt')
                ->with('open_milestone', $projectTask->project_milestone_id);
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete a project task
     */
    public function destroy(ProjectTask $projectTask)
    {
        $milestone = $projectTask->milestone;
        $projectTemplate = $milestone->projectTemplate;
        
        try {
            $projectTask->delete();
            
            return redirect()->route('project-templates.milestones', $projectTemplate)
                ->with('success', 'Taak verwijderd')
                ->with('open_milestone', $milestone->id);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage());
        }
    }

    /**
     * Reorder project tasks
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:project_tasks,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['task_ids'] as $index => $taskId) {
                ProjectTask::where('id', $taskId)->update(['order' => $index]);
            }
            
            DB::commit();
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}