<?php

namespace App\Http\Controllers;

use App\Models\ProjectSubtask;
use App\Models\ProjectTask;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProjectSubtaskController extends Controller
{
    /**
     * Store a new project subtask
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_task_id' => 'required|exists:project_tasks,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fee_type' => 'required|in:in_fee,extended',
            'pricing_type' => 'required|in:fixed_price,hourly_rate',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $task = ProjectTask::findOrFail($validated['project_task_id']);
            
            // Get max order
            $maxOrder = $task->subtasks()->max('order') ?? -1;
            $validated['order'] = $maxOrder + 1;
            
            $subtask = ProjectSubtask::create($validated);
            
            DB::commit();
            
            return redirect()->route('project-templates.milestones', $task->milestone->projectTemplate)
                ->with('success', 'Subtaak toegevoegd')
                ->with('open_milestone', $task->project_milestone_id)
                ->with('open_task', $task->id);
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating project subtask: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update a project subtask
     */
    public function update(Request $request, ProjectSubtask $projectSubtask)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'fee_type' => 'required|in:in_fee,extended',
            'pricing_type' => 'required|in:fixed_price,hourly_rate',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $projectSubtask->update($validated);
            
            DB::commit();
            
            $task = $projectSubtask->task;
            
            return redirect()->route('project-templates.milestones', $task->milestone->projectTemplate)
                ->with('success', 'Subtaak bijgewerkt')
                ->with('open_milestone', $task->project_milestone_id)
                ->with('open_task', $task->id);
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete a project subtask
     */
    public function destroy(ProjectSubtask $projectSubtask)
    {
        $task = $projectSubtask->task;
        $milestone = $task->milestone;
        $projectTemplate = $milestone->projectTemplate;
        
        try {
            $projectSubtask->delete();
            
            return redirect()->route('project-templates.milestones', $projectTemplate)
                ->with('success', 'Subtaak verwijderd')
                ->with('open_milestone', $milestone->id)
                ->with('open_task', $task->id);
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage());
        }
    }

    /**
     * Reorder project subtasks
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'subtask_ids' => 'required|array',
            'subtask_ids.*' => 'exists:project_subtasks,id',
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['subtask_ids'] as $index => $subtaskId) {
                ProjectSubtask::where('id', $subtaskId)->update(['order' => $index]);
            }
            
            DB::commit();
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}