<?php

namespace App\Http\Controllers;

use App\Models\Subtask;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SubtaskController extends Controller
{
    /**
     * Store a newly created subtask
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
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
            'status' => 'nullable|string'
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

            // Get task and inherit dates if not provided
            $task = Task::findOrFail($validated['task_id']);
            
            if (empty($validated['start_date']) && $task->start_date) {
                $validated['start_date'] = $task->start_date;
            }
            if (empty($validated['end_date']) && $task->end_date) {
                $validated['end_date'] = $task->end_date;
            }

            // Handle pricing
            if ($validated['pricing_type'] === 'hourly_rate') {
                $validated['budget'] = ($validated['hourly_rate'] ?? 0) * ($validated['estimated_hours'] ?? 0);
            }
            
            // Map budget to price field if your database uses 'price'
            if (isset($validated['budget'])) {
                $validated['price'] = $validated['budget'];
            }

            // Get the highest order number
            $maxOrder = $task->subtasks()->max('order') ?? -1;
            $validated['order'] = $maxOrder + 1;
            $validated['position'] = $maxOrder + 1;

            // Set defaults
            $validated['status'] = $validated['status'] ?? 'concept';
            $validated['completion_percentage'] = 0;
            $validated['spent_amount'] = 0;
            $validated['remaining_budget'] = $validated['budget'] ?? 0;
            $validated['is_completed'] = false;
            $validated['is_billable'] = true;
            $validated['actual_hours'] = 0;
            $validated['billable_hours'] = 0;

            // Create subtask
            $subtask = Subtask::create($validated);

            // Update task calculations
            if ($task) {
                $task->updateBudgetCalculations();
            }

            DB::commit();

            return redirect()
                ->route('projects.milestones', $task->project)
                ->with('success', 'Subtaak succesvol aangemaakt');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to create subtask: ' . $e->getMessage());
            Log::error('Data: ' . json_encode($request->all()));
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified subtask
     */
    public function update(Request $request, Subtask $subtask)
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
            'spent_amount' => 'nullable|numeric|min:0',
            'actual_hours' => 'nullable|numeric|min:0'
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

            // Map status values
            $statusMap = [
                'concept' => 'concept',
                'in_progress' => 'in_progress',
                'completed' => 'completed'
            ];
            
            if (isset($validated['status'])) {
                $validated['status'] = $statusMap[$validated['status']] ?? $validated['status'];
                
                // Update is_completed based on status
                $validated['is_completed'] = ($validated['status'] === 'completed');
                
                if ($validated['status'] === 'completed' && $subtask->status !== 'completed') {
                    $validated['completed_at'] = now();
                    $validated['completion_percentage'] = 100;
                } elseif ($validated['status'] !== 'completed' && $subtask->status === 'completed') {
                    $validated['completed_at'] = null;
                }
            }

            // Handle pricing
            if ($validated['pricing_type'] === 'hourly_rate') {
                $validated['budget'] = ($validated['hourly_rate'] ?? 0) * ($validated['estimated_hours'] ?? 0);
            }
            
            // Map budget to price field if needed
            if (isset($validated['budget'])) {
                $validated['price'] = $validated['budget'];
            }

            // Update remaining budget
            if (isset($validated['spent_amount'])) {
                $budget = $validated['budget'] ?? $subtask->budget ?? 0;
                $validated['remaining_budget'] = $budget - $validated['spent_amount'];
            }

            $subtask->update($validated);

            // Update parent task calculations
            if ($subtask->task) {
                $subtask->task->updateBudgetCalculations();
            }

            DB::commit();

            return redirect()
                ->route('projects.milestones', $subtask->task->project)
                ->with('success', 'Subtaak succesvol bijgewerkt');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to update subtask: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified subtask
     */
    public function destroy(Subtask $subtask)
    {
        try {
            DB::beginTransaction();

            $task = $subtask->task;
            $project = $task->project;

            $subtask->delete();

            // Update task calculations
            if ($task) {
                $task->updateBudgetCalculations();
            }

            DB::commit();

            return redirect()
                ->route('projects.milestones', $project)
                ->with('success', 'Subtaak succesvol verwijderd');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to delete subtask: ' . $e->getMessage());
            
            return redirect()
                ->back()
                ->with('error', 'Kon subtaak niet verwijderen.');
        }
    }

    /**
     * Toggle subtask status (for checkbox)
     */
    public function toggleStatus(Subtask $subtask)
    {
        try {
            DB::beginTransaction();

            $newStatus = $subtask->status === 'completed' ? 'concept' : 'completed';
            $isCompleted = $newStatus === 'completed';
            
            $subtask->update([
                'status' => $newStatus,
                'is_completed' => $isCompleted,
                'completed_at' => $isCompleted ? now() : null,
                'completion_percentage' => $isCompleted ? 100 : 0
            ]);

            // Update parent task progress
            if ($subtask->task) {
                $subtask->task->updateBudgetCalculations();
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'status' => $newStatus,
                'message' => 'Status succesvol bijgewerkt'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to toggle subtask status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Kon status niet bijwerken'
            ], 500);
        }
    }

    /**
     * Reorder subtasks within a task (for drag & drop)
     */
    public function reorder(Request $request)
    {
        try {
            $validated = $request->validate([
                'subtask_ids' => 'required|array',
                'subtask_ids.*' => 'exists:subtasks,id'
            ]);

            DB::beginTransaction();

            // Update the order
            foreach ($validated['subtask_ids'] as $index => $subtaskId) {
                Subtask::where('id', $subtaskId)->update([
                    'order' => $index,
                    'position' => $index
                ]);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subtaken succesvol geherordend'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to reorder subtasks: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Kon subtaken niet herordenen: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Move a subtask to a different task (for drag & drop)
     */
    public function move(Request $request, Subtask $subtask)
    {
        try {
            $validated = $request->validate([
                'task_id' => 'required|exists:tasks,id'
            ]);

            DB::beginTransaction();

            $newTaskId = $validated['task_id'];
            $oldTaskId = $subtask->task_id;
            
            // Get the new task and verify it's in the same project
            $newTask = Task::findOrFail($newTaskId);
            $oldTask = Task::findOrFail($oldTaskId);
            
            if ($newTask->project_id !== $oldTask->project_id) {
                throw new \Exception('Kan subtaak niet verplaatsen naar taak in ander project');
            }

            // Move the subtask
            $subtask->task_id = $newTaskId;
            
            // Get the highest order number in the new task
            $maxOrder = Subtask::where('task_id', $newTaskId)->max('order') ?? -1;
            $subtask->order = $maxOrder + 1;
            $subtask->position = $maxOrder + 1;
            
            $subtask->save();

            // Update calculations for both tasks
            $oldTask->updateBudgetCalculations();
            $newTask->updateBudgetCalculations();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Subtaak succesvol verplaatst'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to move subtask: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Kon subtaak niet verplaatsen: ' . $e->getMessage()
            ], 500);
        }
    }
}