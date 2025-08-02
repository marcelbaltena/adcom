<?php
// FILE 1: app/Livewire/ProjectDashboard.php

namespace App\Livewire;

use App\Models\Project;
use App\Models\Milestone;
use App\Models\Task;
use App\Models\Subtask;
use App\Models\User;
use Livewire\Component;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ProjectDashboard extends Component
{
    // Filter properties
    public $selectedPeriod = '30'; // days
    public $selectedProject = 'all';
    public $selectedTeamMember = 'all';
    
    // Chart data properties
    public $progressChartData = [];
    public $budgetChartData = [];
    public $teamProductivityData = [];
    public $activityTimelineData = [];
    
    // Summary statistics
    public $totalProjects = 0;
    public $totalMilestones = 0;
    public $totalTasks = 0;
    public $completionRate = 0;
    public $totalBudget = 0;
    public $spentBudget = 0;
    public $teamMembers = 0;
    public $overdueItems = 0;
    
    // Recent activity
    public $recentActivities = [];
    public $upcomingDeadlines = [];
    public $topPerformers = [];

    public function mount()
    {
        $this->loadDashboardData();
    }

    public function updated($property)
    {
        if (in_array($property, ['selectedPeriod', 'selectedProject', 'selectedTeamMember'])) {
            $this->loadDashboardData();
        }
    }

    public function loadDashboardData()
    {
        // Load all dashboard data
        $this->loadSummaryStatistics();
        $this->loadProgressChartData();
        $this->loadBudgetAnalytics();
        $this->loadTeamProductivity();
        $this->loadActivityTimeline();
        $this->loadRecentActivities();
        $this->loadUpcomingDeadlines();
        $this->loadTopPerformers();
    }

    private function loadSummaryStatistics()
    {
        $query = $this->getBaseQuery();
        
        // Total counts
        $this->totalProjects = $query->count();
        $this->totalMilestones = $this->getMilestonesQuery()->count();
        $this->totalTasks = $this->getTasksQuery()->count();
        
        // Completion rates
        $completedMilestones = $this->getMilestonesQuery()->where('status', 'completed')->count();
        $this->completionRate = $this->totalMilestones > 0 ? 
            round(($completedMilestones / $this->totalMilestones) * 100, 1) : 0;
        
        // Budget calculations
        $budgetData = $this->getMilestonesQuery()
            ->selectRaw('SUM(budget) as total_budget, SUM(CASE WHEN status = "completed" THEN budget ELSE 0 END) as spent_budget')
            ->first();
        
        $this->totalBudget = $budgetData->total_budget ?? 0;
        $this->spentBudget = $budgetData->spent_budget ?? 0;
        
        // Team statistics
        $this->teamMembers = User::count();
        
        // Overdue items
        $this->overdueItems = $this->getMilestonesQuery()
            ->where('due_date', '<', now())
            ->where('status', '!=', 'completed')
            ->count();
    }

    private function loadProgressChartData()
    {
        $days = (int) $this->selectedPeriod;
        $startDate = Carbon::now()->subDays($days);
        
        // Daily progress data
        $progressData = [];
        $labels = [];
        
        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('M j');
            
            // Calculate completion percentage for this date
            $totalMilestones = $this->getMilestonesQuery()
                ->where('created_at', '<=', $date->endOfDay())
                ->count();
            
            $completedMilestones = $this->getMilestonesQuery()
                ->where('created_at', '<=', $date->endOfDay())
                ->where('status', 'completed')
                ->count();
            
            $progressData[] = $totalMilestones > 0 ? 
                round(($completedMilestones / $totalMilestones) * 100, 1) : 0;
        }
        
        $this->progressChartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Completion Rate (%)',
                    'data' => $progressData,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'tension' => 0.4,
                    'fill' => true
                ]
            ]
        ];
    }

    private function loadBudgetAnalytics()
    {
        // Budget by status
        $budgetByStatus = $this->getMilestonesQuery()
            ->selectRaw('status, SUM(budget) as total_budget, COUNT(*) as count')
            ->groupBy('status')
            ->get();
        
        $labels = [];
        $data = [];
        $colors = [
            'concept' => '#6B7280',
            'in_progress' => '#F59E0B', 
            'completed' => '#10B981'
        ];
        $backgroundColors = [];
        
        foreach ($budgetByStatus as $item) {
            $labels[] = ucfirst(str_replace('_', ' ', $item->status));
            $data[] = $item->total_budget ?? 0;
            $backgroundColors[] = $colors[$item->status] ?? '#6B7280';
        }
        
        $this->budgetChartData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Budget (€)',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderWidth' => 2,
                    'borderColor' => '#ffffff'
                ]
            ]
        ];
    }

    private function loadTeamProductivity()
    {
        // Get productivity data per team member
        $productivity = DB::table('assignees')
            ->join('users', 'assignees.user_id', '=', 'users.id')
            ->leftJoin('milestones', function($join) {
                $join->on('assignees.assignable_id', '=', 'milestones.id')
                     ->where('assignees.assignable_type', '=', 'App\Models\Milestone');
            })
            ->leftJoin('tasks', function($join) {
                $join->on('assignees.assignable_id', '=', 'tasks.id')
                     ->where('assignees.assignable_type', '=', 'App\Models\Task');
            })
            ->selectRaw('
                users.name,
                users.id,
                COUNT(DISTINCT CASE WHEN assignees.assignable_type = "App\Models\Milestone" THEN assignees.assignable_id END) as milestone_count,
                COUNT(DISTINCT CASE WHEN assignees.assignable_type = "App\Models\Task" THEN assignees.assignable_id END) as task_count,
                COUNT(DISTINCT CASE WHEN assignees.assignable_type = "App\Models\Milestone" AND milestones.status = "completed" THEN milestones.id END) as completed_milestones,
                COUNT(DISTINCT CASE WHEN assignees.assignable_type = "App\Models\Task" AND tasks.status = "completed" THEN tasks.id END) as completed_tasks
            ')
            ->groupBy('users.id', 'users.name')
            ->orderBy('completed_milestones', 'desc')
            ->limit(10)
            ->get();
        
        $labels = [];
        $milestoneData = [];
        $taskData = [];
        
        foreach ($productivity as $user) {
            $labels[] = $user->name;
            $milestoneData[] = $user->completed_milestones ?? 0;
            $taskData[] = $user->completed_tasks ?? 0;
        }
        
        $this->teamProductivityData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Completed Milestones',
                    'data' => $milestoneData,
                    'backgroundColor' => 'rgba(59, 130, 246, 0.8)',
                    'borderColor' => 'rgb(59, 130, 246)',
                    'borderWidth' => 1
                ],
                [
                    'label' => 'Completed Tasks',
                    'data' => $taskData,
                    'backgroundColor' => 'rgba(16, 185, 129, 0.8)',
                    'borderColor' => 'rgb(16, 185, 129)',
                    'borderWidth' => 1
                ]
            ]
        ];
    }

    private function loadActivityTimeline()
    {
        // Activity over time
        $days = (int) $this->selectedPeriod;
        $activities = DB::table('activity_logs')
            ->where('created_at', '>=', Carbon::now()->subDays($days))
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');
        
        $labels = [];
        $data = [];
        
        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $labels[] = Carbon::parse($date)->format('M j');
            $data[] = $activities->get($date)->count ?? 0;
        }
        
        $this->activityTimelineData = [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Activities',
                    'data' => $data,
                    'borderColor' => 'rgb(139, 92, 246)',
                    'backgroundColor' => 'rgba(139, 92, 246, 0.1)',
                    'tension' => 0.4,
                    'fill' => true
                ]
            ]
        ];
    }

    private function loadRecentActivities()
    {
        $this->recentActivities = DB::table('activity_logs')
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->select('activity_logs.*', 'users.name as user_name', 'users.avatar')
            ->orderBy('activity_logs.created_at', 'desc')
            ->limit(10)
            ->get();
    }

    private function loadUpcomingDeadlines()
    {
        $this->upcomingDeadlines = $this->getMilestonesQuery()
            ->where('due_date', '>=', now())
            ->where('due_date', '<=', now()->addDays(14))
            ->where('status', '!=', 'completed')
            ->with(['project', 'assignees.user'])
            ->orderBy('due_date')
            ->limit(8)
            ->get();
    }

    private function loadTopPerformers()
    {
        $this->topPerformers = DB::table('assignees')
            ->join('users', 'assignees.user_id', '=', 'users.id')
            ->leftJoin('milestones', function($join) {
                $join->on('assignees.assignable_id', '=', 'milestones.id')
                     ->where('assignees.assignable_type', '=', 'App\Models\Milestone')
                     ->where('milestones.status', '=', 'completed');
            })
            ->leftJoin('tasks', function($join) {
                $join->on('assignees.assignable_id', '=', 'tasks.id')
                     ->where('assignees.assignable_type', '=', 'App\Models\Task')
                     ->where('tasks.status', '=', 'completed');
            })
            ->selectRaw('
                users.id,
                users.name,
                users.avatar,
                COUNT(DISTINCT milestones.id) + COUNT(DISTINCT tasks.id) as completed_items,
                COUNT(DISTINCT milestones.id) as completed_milestones,
                COUNT(DISTINCT tasks.id) as completed_tasks
            ')
            ->groupBy('users.id', 'users.name', 'users.avatar')
            ->orderBy('completed_items', 'desc')
            ->limit(5)
            ->get();
    }

    // Helper methods for building queries
    private function getBaseQuery()
    {
        $query = Project::query();
        
        if ($this->selectedProject !== 'all') {
            $query->where('id', $this->selectedProject);
        }
        
        return $query;
    }

    private function getMilestonesQuery()
    {
        $query = Milestone::query();
        
        if ($this->selectedProject !== 'all') {
            $query->where('project_id', $this->selectedProject);
        }
        
        if ($this->selectedTeamMember !== 'all') {
            $query->whereHas('assignees', function($q) {
                $q->where('user_id', $this->selectedTeamMember);
            });
        }
        
        return $query;
    }

    private function getTasksQuery()
    {
        $query = Task::query();
        
        if ($this->selectedProject !== 'all') {
            $query->whereHas('milestone', function($q) {
                $q->where('project_id', $this->selectedProject);
            });
        }
        
        if ($this->selectedTeamMember !== 'all') {
            $query->whereHas('assignees', function($q) {
                $q->where('user_id', $this->selectedTeamMember);
            });
        }
        
        return $query;
    }

    public function render()
    {
        $projects = Project::select('id', 'name')->get();
        $users = User::select('id', 'name')->get();
        
        return view('livewire.project-dashboard', compact('projects', 'users'));
    }
}

