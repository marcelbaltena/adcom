<?php

namespace App\Http\Controllers;

use App\Models\Milestone;
use App\Models\Task;
use App\Models\BudgetAllocation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class BudgetController extends Controller
{
    /**
     * Get milestone budget data for editing
     */
    public function getMilestoneBudget(Milestone $milestone)
    {
        try {
            $milestone->load('project', 'tasks');
            
            return response()->json([
                'id' => $milestone->id,
                'title' => $milestone->title,
                'start_date' => $milestone->start_date,
                'end_date' => $milestone->end_date,
                'actual_start_date' => $milestone->actual_start_date,
                'actual_end_date' => $milestone->actual_end_date,
                'fee_type' => $milestone->fee_type,
                'pricing_type' => $milestone->pricing_type,
                'price' => $milestone->price,
                'hourly_rate' => $milestone->hourly_rate,
                'estimated_hours' => $milestone->estimated_hours,
                'completion_percentage' => $milestone->completion_percentage,
                'budget_status' => $milestone->budget_status,
                'budget_notes' => $milestone->budget_notes,
                'allocated_budget' => $milestone->allocated_budget,
                'spent' => $milestone->spent,
                'remaining_budget' => $milestone->price - $milestone->spent,
                'project_budget' => $milestone->project->budget,
                'project_currency' => $milestone->project->currency,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching milestone budget: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch milestone data'], 500);
        }
    }

    /**
     * Update milestone budget
     */
    public function updateMilestoneBudget(Request $request, Milestone $milestone)
    {
        $request->validate([
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'actual_start_date' => 'nullable|date',
            'actual_end_date' => 'nullable|date|after_or_equal:actual_start_date',
            'fee_type' => 'required|in:in_fee,extended_fee',
            'pricing_type' => 'required|in:fixed_price,hourly_rate',
            'price' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|numeric|min:0',
            'completion_percentage' => 'nullable|integer|min:0|max:100',
            'budget_status' => 'required|in:under,on_track,warning,over',
            'budget_notes' => 'nullable|string|max:1000',
        ]);

        try {
            DB::beginTransaction();

            // Calculate price based on pricing type
            $price = $milestone->price;
            if ($request->pricing_type === 'hourly_rate') {
                $price = ($request->hourly_rate ?? 0) * ($request->estimated_hours ?? 0);
            } else {
                $price = $request->price ?? 0;
            }

            // Validate budget allocation if in project fee
            if ($request->fee_type === 'in_fee') {
                $validation = $this->validateProjectBudgetAllocation($milestone, $price);
                if (!$validation['valid']) {
                    return back()->withErrors(['budget' => $validation['message']]);
                }
            }

            // Update milestone
            $milestone->update([
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'actual_start_date' => $request->actual_start_date,
                'actual_end_date' => $request->actual_end_date,
                'fee_type' => $request->fee_type,
                'pricing_type' => $request->pricing_type,
                'price' => $price,
                'hourly_rate' => $request->hourly_rate,
                'estimated_hours' => $request->estimated_hours,
                'completion_percentage' => $request->completion_percentage,
                'budget_status' => $request->budget_status,
                'budget_notes' => $request->budget_notes,
            ]);

            // Recalculate project budget allocation
            $milestone->project->recalculateBudget();

            // Create budget allocation record
            BudgetAllocation::create([
                'allocatable_type' => Milestone::class,
                'allocatable_id' => $milestone->id,
                'amount' => $price,
                'allocation_type' => 'budget_update',
                'allocation_date' => now(),
                'status' => 'approved',
                'notes' => $request->budget_notes,
                'metadata' => [
                    'old_price' => $milestone->getOriginal('price'),
                    'new_price' => $price,
                    'fee_type' => $request->fee_type,
                    'pricing_type' => $request->pricing_type,
                ]
            ]);

            DB::commit();

            return back()->with('success', 'Milestone budget updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating milestone budget: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update milestone budget. Please try again.']);
        }
    }

    /**
     * Get task budget data for editing
     */
    public function getTaskBudget(Task $task)
    {
        try {
            $task->load('milestone.project');
            
            return response()->json([
                'id' => $task->id,
                'title' => $task->title,
                'fee_type' => $task->fee_type,
                'pricing_type' => $task->pricing_type,
                'price' => $task->price,
                'hourly_rate' => $task->hourly_rate,
                'estimated_hours' => $task->estimated_hours,
                'completion_percentage' => $task->completion_percentage,
                'spent' => $task->spent ?? 0,
                'milestone_budget' => $task->milestone->price,
                'project_currency' => $task->milestone->project->currency,
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching task budget: ' . $e->getMessage());
            return response()->json(['error' => 'Unable to fetch task data'], 500);
        }
    }

    /**
     * Update task budget
     */
    public function updateTaskBudget(Request $request, Task $task)
    {
        $request->validate([
            'fee_type' => 'required|in:in_fee,extended_fee',
            'pricing_type' => 'required|in:fixed_price,hourly_rate',
            'price' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|numeric|min:0',
            'completion_percentage' => 'nullable|integer|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();

            // Calculate price based on pricing type
            $price = $task->price;
            if ($request->pricing_type === 'hourly_rate') {
                $price = ($request->hourly_rate ?? 0) * ($request->estimated_hours ?? 0);
            } else {
                $price = $request->price ?? 0;
            }

            // Validate task budget against milestone if in milestone fee
            if ($request->fee_type === 'in_fee') {
                $validation = $this->validateTaskBudgetAllocation($task, $price);
                if (!$validation['valid']) {
                    return back()->withErrors(['budget' => $validation['message']]);
                }
            }

            // Update task
            $task->update([
                'fee_type' => $request->fee_type,
                'pricing_type' => $request->pricing_type,
                'price' => $price,
                'hourly_rate' => $request->hourly_rate,
                'estimated_hours' => $request->estimated_hours,
                'completion_percentage' => $request->completion_percentage,
            ]);

            // Recalculate milestone budget allocation
            $task->milestone->recalculateBudget();

            DB::commit();

            return back()->with('success', 'Task budget updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating task budget: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update task budget. Please try again.']);
        }
    }

    /**
     * Validate milestone budget allocation
     */
    public function validateMilestoneBudget(Request $request)
    {
        try {
            $milestoneId = $request->milestone_id;
            $feeType = $request->fee_type;
            $price = $request->price ?? 0;
            $hourlyRate = $request->hourly_rate ?? 0;
            $estimatedHours = $request->estimated_hours ?? 0;

            if ($request->pricing_type === 'hourly_rate') {
                $price = $hourlyRate * $estimatedHours;
            }

            $milestone = Milestone::findOrFail($milestoneId);
            $validation = $this->validateProjectBudgetAllocation($milestone, $price, $feeType);

            return response()->json($validation);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Error validating budget: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Validate task budget allocation
     */
    public function validateTaskBudget(Request $request)
    {
        try {
            $taskId = $request->task_id;
            $feeType = $request->fee_type;
            $price = $request->price ?? 0;

            if ($request->pricing_type === 'hourly_rate') {
                $price = ($request->hourly_rate ?? 0) * ($request->estimated_hours ?? 0);
            }

            $task = Task::findOrFail($taskId);
            $validation = $this->validateTaskBudgetAllocation($task, $price, $feeType);

            return response()->json($validation);

        } catch (\Exception $e) {
            return response()->json([
                'valid' => false,
                'message' => 'Error validating task budget: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Create budget allocation
     */
    public function createAllocation(Request $request)
    {
        $request->validate([
            'allocatable_type' => 'required|string',
            'allocatable_id' => 'required|integer',
            'amount' => 'required|numeric|min:0',
            'allocation_type' => 'required|in:initial,adjustment,reallocation',
            'notes' => 'nullable|string|max:1000',
        ]);

        try {
            $allocation = BudgetAllocation::create([
                'allocatable_type' => $request->allocatable_type,
                'allocatable_id' => $request->allocatable_id,
                'amount' => $request->amount,
                'allocation_type' => $request->allocation_type,
                'allocation_date' => now(),
                'status' => 'pending',
                'notes' => $request->notes,
                'user_id' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'allocation' => $allocation,
                'message' => 'Budget allocation created successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error creating budget allocation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create budget allocation'
            ], 500);
        }
    }

    /**
     * Approve budget allocation
     */
    public function approveAllocation(BudgetAllocation $allocation)
    {
        try {
            $allocation->update([
                'status' => 'approved',
                'approved_at' => now(),
                'approved_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Budget allocation approved successfully'
            ]);

        } catch (\Exception $e) {
            Log::error('Error approving budget allocation: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve budget allocation'
            ], 500);
        }
    }

    /**
     * Generate project budget report
     */
    public function projectBudgetReport($projectId)
    {
        try {
            $project = \App\Models\Project::with([
                'milestones.tasks',
                'customer',
                'billingCompany'
            ])->findOrFail($projectId);

            $report = [
                'project' => $project,
                'budget_summary' => [
                    'total_budget' => $project->budget,
                    'allocated_budget' => $project->allocated_budget,
                    'spent_budget' => $project->spent,
                    'remaining_budget' => $project->remaining_budget,
                    'budget_variance' => $project->getBudgetVariance(),
                    'budget_status' => $project->budget_status,
                ],
                'milestones' => $project->milestones->map(function ($milestone) {
                    return [
                        'id' => $milestone->id,
                        'title' => $milestone->title,
                        'budget' => $milestone->price,
                        'spent' => $milestone->spent,
                        'completion' => $milestone->completion_percentage,
                        'status' => $milestone->budget_status,
                        'tasks_count' => $milestone->tasks->count(),
                        'tasks_budget' => $milestone->tasks->sum('price'),
                    ];
                }),
            ];

            return response()->json($report);

        } catch (\Exception $e) {
            Log::error('Error generating budget report: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to generate budget report'
            ], 500);
        }
    }

    /**
     * Private: Validate project budget allocation
     */
    private function validateProjectBudgetAllocation(Milestone $milestone, $newPrice, $feeType = null)
    {
        if ($feeType === 'extended_fee') {
            return ['valid' => true, 'message' => 'Extended fee - no project budget limit'];
        }

        $project = $milestone->project;
        $currentMilestoneBudget = $feeType === 'in_fee' ? $milestone->price : 0;
        $otherMilestonesTotal = $project->milestones()
            ->where('id', '!=', $milestone->id)
            ->where('fee_type', 'in_fee')
            ->sum('price');

        $totalAllocation = $otherMilestonesTotal + $newPrice;
        $tolerance = $project->budget * ($project->budget_tolerance_percentage / 100);
        $maxAllowedBudget = $project->budget + $tolerance;

        if ($totalAllocation > $maxAllowedBudget) {
            $overage = $totalAllocation - $project->budget;
            return [
                'valid' => false,
                'message' => "Budget allocation exceeds project budget by €" . number_format($overage, 2) . 
                           ". Maximum allowed (with {$project->budget_tolerance_percentage}% tolerance): €" . 
                           number_format($maxAllowedBudget, 2)
            ];
        }

        if ($totalAllocation > $project->budget) {
            $overage = $totalAllocation - $project->budget;
            return [
                'valid' => true,
                'warning' => true,
                'message' => "Budget allocation exceeds project budget by €" . number_format($overage, 2) . 
                           " but is within tolerance limits."
            ];
        }

        return ['valid' => true, 'message' => 'Budget allocation is valid'];
    }

    /**
     * Private: Validate task budget allocation
     */
    private function validateTaskBudgetAllocation(Task $task, $newPrice, $feeType = null)
    {
        if ($feeType === 'extended_fee') {
            return ['valid' => true, 'message' => 'Extended fee - no milestone budget limit'];
        }

        $milestone = $task->milestone;
        $otherTasksTotal = $milestone->tasks()
            ->where('id', '!=', $task->id)
            ->where('fee_type', 'in_fee')
            ->sum('price');

        $totalAllocation = $otherTasksTotal + $newPrice;

        if ($totalAllocation > $milestone->price) {
            $overage = $totalAllocation - $milestone->price;
            return [
                'valid' => false,
                'message' => "Task budget allocation exceeds milestone budget by €" . number_format($overage, 2) . 
                           ". Milestone budget: €" . number_format($milestone->price, 2)
            ];
        }

        return ['valid' => true, 'message' => 'Task budget allocation is valid'];
    }
}
