<?php

namespace App\Http\Controllers;

use App\Models\TimeEntry;
use App\Models\Milestone;
use App\Models\Task;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TimeEntryController extends Controller
{
    /**
     * Display a listing of time entries
     */
    public function index(Request $request)
    {
        $query = TimeEntry::with(['trackable', 'user'])
            ->where('user_id', auth()->id())
            ->orderBy('date', 'desc');

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        // Filter by billable status
        if ($request->filled('billable')) {
            $query->where('is_billable', $request->billable === 'yes');
        }

        $timeEntries = $query->paginate(20);

        return view('time-entries.index', compact('timeEntries'));
    }

    /**
     * Store time entry for milestone
     */
    public function storeForMilestone(Request $request, Milestone $milestone)
    {
        $request->validate([
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.25|max:24',
            'description' => 'required|string|max:1000',
            'is_billable' => 'boolean',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Get default hourly rate if not provided
            $hourlyRate = $request->hourly_rate ?? 
                         $milestone->hourly_rate ?? 
                         auth()->user()->hourly_rate ?? 
                         $milestone->project->customer->hourly_rate ?? 
                         75.00;

            $timeEntry = TimeEntry::create([
                'trackable_type' => Milestone::class,
                'trackable_id' => $milestone->id,
                'user_id' => auth()->id(),
                'date' => $request->date,
                'hours' => $request->hours,
                'description' => $request->description,
                'is_billable' => $request->boolean('is_billable', true),
                'hourly_rate' => $hourlyRate,
                'total_cost' => $request->hours * $hourlyRate,
                'status' => 'submitted',
            ]);

            // Update milestone spent amount
            $milestone->increment('spent', $timeEntry->total_cost);
            $milestone->increment('actual_hours', $request->hours);

            // Update project spent amount
            $milestone->project->increment('spent', $timeEntry->total_cost);

            // Recalculate budget status
            $milestone->updateBudgetStatus();
            $milestone->project->updateBudgetStatus();

            DB::commit();

            return back()->with('success', 'Time entry logged successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating time entry for milestone: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to log time entry. Please try again.']);
        }
    }

    /**
     * Store time entry for task
     */
    public function storeForTask(Request $request, Task $task)
    {
        $request->validate([
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.25|max:24',
            'description' => 'required|string|max:1000',
            'is_billable' => 'boolean',
            'hourly_rate' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Get default hourly rate if not provided
            $hourlyRate = $request->hourly_rate ?? 
                         $task->hourly_rate ?? 
                         $task->milestone->hourly_rate ??
                         auth()->user()->hourly_rate ?? 
                         $task->milestone->project->customer->hourly_rate ?? 
                         75.00;

            $timeEntry = TimeEntry::create([
                'trackable_type' => Task::class,
                'trackable_id' => $task->id,
                'user_id' => auth()->id(),
                'date' => $request->date,
                'hours' => $request->hours,
                'description' => $request->description,
                'is_billable' => $request->boolean('is_billable', true),
                'hourly_rate' => $hourlyRate,
                'total_cost' => $request->hours * $hourlyRate,
                'status' => 'submitted',
            ]);

            // Update task spent amount
            $task->increment('spent', $timeEntry->total_cost);
            $task->increment('actual_hours', $request->hours);

            // Update milestone and project spent amounts
            $task->milestone->increment('spent', $timeEntry->total_cost);
            $task->milestone->project->increment('spent', $timeEntry->total_cost);

            // Recalculate budget statuses
            $task->updateBudgetStatus();
            $task->milestone->updateBudgetStatus();
            $task->milestone->project->updateBudgetStatus();

            DB::commit();

            return back()->with('success', 'Time entry logged successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating time entry for task: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to log time entry. Please try again.']);
        }
    }

    /**
     * Display the specified time entry
     */
    public function show(TimeEntry $timeEntry)
    {
        $this->authorize('view', $timeEntry);
        
        $timeEntry->load(['trackable', 'user']);
        
        return view('time-entries.show', compact('timeEntry'));
    }

    /**
     * Show the form for editing the specified time entry
     */
    public function edit(TimeEntry $timeEntry)
    {
        $this->authorize('update', $timeEntry);
        
        $timeEntry->load(['trackable']);
        
        return view('time-entries.edit', compact('timeEntry'));
    }

    /**
     * Update the specified time entry
     */
    public function update(Request $request, TimeEntry $timeEntry)
    {
        $this->authorize('update', $timeEntry);

        $request->validate([
            'date' => 'required|date',
            'hours' => 'required|numeric|min:0.25|max:24',
            'description' => 'required|string|max:1000',
            'is_billable' => 'boolean',
            'hourly_rate' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Calculate differences for updates
            $oldCost = $timeEntry->total_cost;
            $oldHours = $timeEntry->hours;
            $newCost = $request->hours * $request->hourly_rate;
            $costDifference = $newCost - $oldCost;
            $hoursDifference = $request->hours - $oldHours;

            // Update time entry
            $timeEntry->update([
                'date' => $request->date,
                'hours' => $request->hours,
                'description' => $request->description,
                'is_billable' => $request->boolean('is_billable'),
                'hourly_rate' => $request->hourly_rate,
                'total_cost' => $newCost,
            ]);

            // Update spent amounts and hours
            $trackable = $timeEntry->trackable;
            
            if ($trackable instanceof Task) {
                $trackable->increment('spent', $costDifference);
                $trackable->increment('actual_hours', $hoursDifference);
                $trackable->milestone->increment('spent', $costDifference);
                $trackable->milestone->project->increment('spent', $costDifference);
                
                // Recalculate budget statuses
                $trackable->updateBudgetStatus();
                $trackable->milestone->updateBudgetStatus();
                $trackable->milestone->project->updateBudgetStatus();
                
            } elseif ($trackable instanceof Milestone) {
                $trackable->increment('spent', $costDifference);
                $trackable->increment('actual_hours', $hoursDifference);
                $trackable->project->increment('spent', $costDifference);
                
                // Recalculate budget statuses
                $trackable->updateBudgetStatus();
                $trackable->project->updateBudgetStatus();
            }

            DB::commit();

            return back()->with('success', 'Time entry updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating time entry: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update time entry. Please try again.']);
        }
    }

    /**
     * Remove the specified time entry from storage
     */
    public function destroy(TimeEntry $timeEntry)
    {
        $this->authorize('delete', $timeEntry);

        try {
            DB::beginTransaction();

            // Reverse the spent amounts and hours
            $trackable = $timeEntry->trackable;
            
            if ($trackable instanceof Task) {
                $trackable->decrement('spent', $timeEntry->total_cost);
                $trackable->decrement('actual_hours', $timeEntry->hours);
                $trackable->milestone->decrement('spent', $timeEntry->total_cost);
                $trackable->milestone->project->decrement('spent', $timeEntry->total_cost);
                
                // Recalculate budget statuses
                $trackable->updateBudgetStatus();
                $trackable->milestone->updateBudgetStatus();
                $trackable->milestone->project->updateBudgetStatus();
                
            } elseif ($trackable instanceof Milestone) {
                $trackable->decrement('spent', $timeEntry->total_cost);
                $trackable->decrement('actual_hours', $timeEntry->hours);
                $trackable->project->decrement('spent', $timeEntry->total_cost);
                
                // Recalculate budget statuses
                $trackable->updateBudgetStatus();
                $trackable->project->updateBudgetStatus();
            }

            // Delete the time entry
            $timeEntry->delete();

            DB::commit();

            return back()->with('success', 'Time entry deleted successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting time entry: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete time entry. Please try again.']);
        }
    }
}