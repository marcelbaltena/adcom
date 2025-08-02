<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class ProjectTeamController extends Controller
{
    /**
     * Display project team members
     */
    public function index(Project $project)
    {
        // Check permissions
        $this->authorize('viewTeam', $project);
        
        $project->load([
            'teamMembers.company',
            'owner',
            'billingCompany',
            'customer'
        ]);
        
        // Get team members with their roles and stats
        $teamMembers = $project->teamMembers()
            ->with(['company'])
            ->withCount([
                'assignedTasks' => function ($query) use ($project) {
                    $query->where('project_id', $project->id);
                },
                'assignedSubtasks' => function ($query) use ($project) {
                    $query->whereHas('task', function ($q) use ($project) {
                        $q->where('project_id', $project->id);
                    });
                }
            ])
            ->get();
        
        // Get available users (not already in team)
        $availableUsers = User::where('is_active', true)
            ->whereNotIn('id', $teamMembers->pluck('id'))
            ->when(auth()->user()->role !== 'admin', function ($query) use ($project) {
                // Non-admins can only see users from related companies
                $query->whereIn('company_id', [
                    $project->billing_company_id,
                    $project->created_by_company_id,
                    auth()->user()->company_id
                ]);
            })
            ->with('company')
            ->orderBy('name')
            ->get();
        
        // Get project statistics
        $stats = [
            'total_tasks' => $project->tasks()->count(),
            'completed_tasks' => $project->tasks()->where('status', 'voltooid')->count(),
            'total_milestones' => $project->milestones()->count(),
            'completed_milestones' => $project->milestones()->where('status', 'completed')->count(),
        ];
        
        return view('projects.team.index', compact('project', 'teamMembers', 'availableUsers', 'stats'));
    }

    /**
     * Add team member to project
     */
    public function store(Request $request, Project $project)
    {
        $this->authorize('manageTeam', $project);
        
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => ['required', Rule::in(['project_manager', 'team_member', 'viewer'])],
            'permissions' => 'nullable|array',
            'send_notification' => 'boolean',
        ]);

        try {
            DB::beginTransaction();
            
            $user = User::findOrFail($validated['user_id']);
            
            // Check if user is already in team
            if ($project->hasTeamMember($user)) {
                return redirect()->back()
                    ->with('error', 'Deze gebruiker is al lid van het projectteam.');
            }
            
            // Add team member
            $project->addTeamMember($user, $validated['role'], $validated['permissions'] ?? []);
            
            // Add as watcher if they're not a viewer
            if ($validated['role'] !== 'viewer') {
                $project->addWatcher($user);
            }
            
            // Send notification if requested
            if ($request->boolean('send_notification')) {
                // You can implement notification logic here
                // $user->notify(new AddedToProjectTeam($project));
            }
            
            DB::commit();
            
            return redirect()->route('projects.team.index', $project)
                ->with('success', "{$user->name} is toegevoegd aan het projectteam als {$validated['role']}!");
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error adding team member: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden bij het toevoegen van het teamlid.');
        }
    }

    /**
     * Update team member role and permissions
     */
    public function update(Request $request, Project $project, User $user)
    {
        $this->authorize('manageTeam', $project);
        
        $validated = $request->validate([
            'role' => ['required', Rule::in(['project_manager', 'team_member', 'viewer'])],
            'permissions' => 'nullable|array',
        ]);

        try {
            // Prevent removing the last project manager
            if ($project->getTeamMemberRole($user) === 'project_manager' && 
                $validated['role'] !== 'project_manager' &&
                $project->getProjectManagers()->count() === 1) {
                return redirect()->back()
                    ->with('error', 'Er moet minimaal één project manager zijn.');
            }
            
            // Update role and permissions
            $project->updateTeamMemberRole($user, $validated['role'], $validated['permissions'] ?? []);
            
            // Update watcher status based on role
            if ($validated['role'] === 'viewer') {
                $project->removeWatcher($user);
            } else {
                $project->addWatcher($user);
            }
            
            return redirect()->route('projects.team.index', $project)
                ->with('success', "Rol van {$user->name} is bijgewerkt naar {$validated['role']}!");
                
        } catch (\Exception $e) {
            Log::error('Error updating team member: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden bij het bijwerken van het teamlid.');
        }
    }

    /**
     * Remove team member from project
     */
    public function destroy(Project $project, User $user)
    {
        $this->authorize('manageTeam', $project);

        try {
            // Prevent removing the project owner
            if ($project->user_id === $user->id) {
                return redirect()->back()
                    ->with('error', 'De project eigenaar kan niet uit het team worden verwijderd.');
            }
            
            // Prevent removing the last project manager
            if ($project->getTeamMemberRole($user) === 'project_manager' &&
                $project->getProjectManagers()->count() === 1) {
                return redirect()->back()
                    ->with('error', 'Er moet minimaal één project manager zijn.');
            }
            
            DB::beginTransaction();
            
            // Remove from team
            $project->removeTeamMember($user);
            
            // Remove as watcher
            $project->removeWatcher($user);
            
            // Note: We don't unassign tasks/subtasks automatically
            // This should be handled separately if needed
            
            DB::commit();
            
            return redirect()->route('projects.team.index', $project)
                ->with('success', "{$user->name} is verwijderd uit het projectteam!");
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error removing team member: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden bij het verwijderen van het teamlid.');
        }
    }

    /**
     * Show team member details and activity
     */
    public function show(Project $project, User $user)
    {
        $this->authorize('viewTeam', $project);
        
        // Check if user is team member
        if (!$project->hasTeamMember($user)) {
            abort(404);
        }
        
        $teamMember = $project->projectTeams()
            ->where('user_id', $user->id)
            ->first();
        
        // Get user's tasks in this project
        $tasks = $project->tasks()
            ->whereHas('assignees', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            })
            ->with(['milestone', 'subtasks'])
            ->get();
        
        // Get user's activity in this project
        $activities = $project->activityLogs()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(20)
            ->get();
        
        // Get user's comments in this project
        $comments = $project->comments()
            ->where('user_id', $user->id)
            ->latest()
            ->limit(10)
            ->get();
        
        // Calculate statistics
        $stats = [
            'assigned_tasks' => $tasks->count(),
            'completed_tasks' => $tasks->where('status', 'voltooid')->count(),
            'total_hours' => $tasks->sum('actual_hours'),
            'activities_count' => $project->activityLogs()->where('user_id', $user->id)->count(),
            'comments_count' => $project->comments()->where('user_id', $user->id)->count(),
        ];
        
        return view('projects.team.show', compact(
            'project',
            'user',
            'teamMember',
            'tasks',
            'activities',
            'comments',
            'stats'
        ));
    }

    /**
     * Bulk assign tasks to team member
     */
    public function assignTasks(Request $request, Project $project, User $user)
    {
        $this->authorize('manageTeam', $project);
        
        $validated = $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:tasks,id',
            'role' => ['nullable', Rule::in(['lead', 'assignee', 'reviewer'])],
        ]);

        try {
            DB::beginTransaction();
            
            $role = $validated['role'] ?? 'assignee';
            $assignedCount = 0;
            
            foreach ($validated['task_ids'] as $taskId) {
                $task = $project->tasks()->find($taskId);
                
                if ($task && !$task->isAssignedTo($user)) {
                    $task->assignUser($user, $role);
                    $assignedCount++;
                }
            }
            
            DB::commit();
            
            return redirect()->back()
                ->with('success', "{$assignedCount} taken toegewezen aan {$user->name}!");
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error assigning tasks: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden bij het toewijzen van taken.');
        }
    }

    /**
     * Get team member permissions (AJAX)
     */
    public function getPermissions(Project $project, User $user)
    {
        $this->authorize('manageTeam', $project);
        
        $teamMember = $project->projectTeams()
            ->where('user_id', $user->id)
            ->first();
        
        if (!$teamMember) {
            return response()->json(['error' => 'Team member not found'], 404);
        }
        
        return response()->json([
            'role' => $teamMember->role,
            'permissions' => $teamMember->permissions ?? [],
            'available_permissions' => $this->getAvailableProjectPermissions(),
        ]);
    }

    /**
     * Search users for adding to team (AJAX)
     */
    public function searchUsers(Request $request, Project $project)
    {
        $this->authorize('manageTeam', $project);
        
        $search = $request->get('q', '');
        
        $users = User::where('is_active', true)
            ->whereNotIn('id', $project->teamMembers->pluck('id'))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->when(auth()->user()->role !== 'admin', function ($query) use ($project) {
                // Non-admins can only see users from related companies
                $query->whereIn('company_id', [
                    $project->billing_company_id,
                    $project->created_by_company_id,
                    auth()->user()->company_id
                ]);
            })
            ->with('company')
            ->limit(10)
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'text' => $user->name . ' (' . $user->email . ')',
                    'company' => $user->company->name ?? 'Geen bedrijf',
                    'role' => $user->display_role,
                ];
            });
        
        return response()->json(['results' => $users]);
    }

    /**
     * Export team members list
     */
    public function export(Project $project)
    {
        $this->authorize('viewTeam', $project);
        
        $teamMembers = $project->teamMembers()
            ->with(['company'])
            ->withCount([
                'assignedTasks' => function ($query) use ($project) {
                    $query->where('project_id', $project->id);
                },
                'assignedSubtasks' => function ($query) use ($project) {
                    $query->whereHas('task', function ($q) use ($project) {
                        $q->where('project_id', $project->id);
                    });
                }
            ])
            ->get();
        
        $filename = 'project_team_' . $project->id . '_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($teamMembers, $project) {
            $file = fopen('php://output', 'w');
            
            // Project info
            fputcsv($file, ['Project:', $project->name]);
            fputcsv($file, ['Klant:', $project->customer->name ?? '-']);
            fputcsv($file, ['']);
            
            // Headers
            fputcsv($file, [
                'Naam', 'Email', 'Bedrijf', 'Rol in project', 'Toegewezen taken',
                'Toegewezen subtaken', 'Lid sinds'
            ]);
            
            // Data
            foreach ($teamMembers as $member) {
                fputcsv($file, [
                    $member->name,
                    $member->email,
                    $member->company->name ?? '-',
                    $member->pivot->role,
                    $member->assigned_tasks_count,
                    $member->assigned_subtasks_count,
                    $member->pivot->created_at->format('Y-m-d'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get available project permissions
     */
    private function getAvailableProjectPermissions(): array
    {
        return [
            'milestones' => [
                'create' => 'Milestones aanmaken',
                'edit' => 'Milestones bewerken',
                'delete' => 'Milestones verwijderen',
            ],
            'tasks' => [
                'create' => 'Taken aanmaken',
                'edit' => 'Taken bewerken',
                'delete' => 'Taken verwijderen',
                'assign' => 'Taken toewijzen',
            ],
            'budget' => [
                'view' => 'Budget bekijken',
                'edit' => 'Budget bewerken',
            ],
            'team' => [
                'view' => 'Team bekijken',
                'manage' => 'Team beheren',
            ],
            'files' => [
                'upload' => 'Bestanden uploaden',
                'delete' => 'Bestanden verwijderen',
            ],
        ];
    }
}