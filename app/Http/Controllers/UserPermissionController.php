<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Project;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserPermissionController extends Controller
{
    /**
     * Check if user is admin
     */
    private function checkAdmin()
    {
        if (!auth()->check() || auth()->user()->role !== 'admin') {
            abort(403, 'Geen toegang - alleen voor administrators');
        }
    }

    /**
     * Display a listing of users with their permissions
     */
    public function index(Request $request)
    {
        $this->checkAdmin();

        $query = User::with(['company', 'projectTeams.project']);

        // Filter by company
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        // Filter by role
        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        // Filter by active status
        if ($request->filled('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('name')->paginate(20);
        
        $companies = Company::orderBy('name')->get();
        $roles = ['admin', 'beheerder', 'account_manager', 'user'];
        
        return view('admin.users.permissions.index', compact('users', 'companies', 'roles'));
    }

    /**
     * Show the form for editing user permissions
     */
    public function edit(User $user)
    {
        $this->checkAdmin();

        $user->load(['company', 'projectTeams']);
        
        $permissions = $this->getAvailablePermissions();
        $projects = Project::with('customer')->orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        
        // Get user's current permissions organized by category
        $userPermissions = $this->organizeUserPermissions($user);
        
        return view('admin.users.permissions.edit', compact(
            'user', 
            'permissions', 
            'projects', 
            'companies',
            'userPermissions'
        ));
    }

    /**
     * Update user permissions
     */
    public function update(Request $request, User $user)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'role' => ['required', Rule::in(['admin', 'beheerder', 'account_manager', 'user'])],
            'company_id' => 'nullable|exists:companies,id',
            'permissions' => 'nullable|array',
            'can_see_all_projects' => 'boolean',
            'can_see_financial_data' => 'boolean',
            'project_access' => 'nullable|array',
            'project_access.*' => 'exists:projects,id',
            'project_roles' => 'nullable|array',
            'project_roles.*' => Rule::in(['project_manager', 'team_member', 'viewer']),
        ]);

        try {
            DB::beginTransaction();

            // Update basic user info
            $user->update([
                'role' => $validated['role'],
                'company_id' => $validated['company_id'] ?? $user->company_id,
                'can_see_all_projects' => $request->boolean('can_see_all_projects'),
                'can_see_financial_data' => $request->boolean('can_see_financial_data'),
            ]);

            // Update custom permissions
            $this->updateUserPermissions($user, $validated['permissions'] ?? []);

            // Update project access
            $this->updateProjectAccess($user, 
                $validated['project_access'] ?? [], 
                $validated['project_roles'] ?? []
            );

            // Log activity - temporarily disabled if activity() helper doesn't exist
            // Uncomment when you have spatie/laravel-activitylog installed
            /*
            activity()
                ->performedOn($user)
                ->causedBy(auth()->user())
                ->withProperties([
                    'role' => $validated['role'],
                    'permissions_updated' => true
                ])
                ->log('User permissions updated');
            */

            DB::commit();

            return redirect()->route('admin.users.permissions.index')
                ->with('success', 'Gebruikersrechten succesvol bijgewerkt!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating user permissions: ' . $e->getMessage());
            
            return redirect()->back()
                ->withInput()
                ->with('error', 'Er is een fout opgetreden bij het bijwerken van de rechten.');
        }
    }

    /**
     * Show role permissions management
     */
    public function roles(Request $request)
    {
        $this->checkAdmin();

        $roles = RolePermission::getAvailableRoles();
        $selectedRole = $request->get('role', 'user');
        
        $permissions = RolePermission::where('role', $selectedRole)
            ->orderBy('resource')
            ->orderBy('action')
            ->get();
            
        $resources = RolePermission::getAvailableResources();
        $actions = RolePermission::getAvailableActions();
        
        return view('admin.users.permissions.roles', compact(
            'roles',
            'selectedRole',
            'permissions',
            'resources',
            'actions'
        ));
    }

    /**
     * Update role permissions
     */
    public function updateRole(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'role' => ['required', Rule::in(RolePermission::getAvailableRoles())],
            'permissions' => 'nullable|array',
        ]);

        try {
            DB::beginTransaction();

            $role = $validated['role'];
            
            // Don't allow editing admin permissions
            if ($role === 'admin') {
                return redirect()->back()
                    ->with('warning', 'Admin rechten kunnen niet worden aangepast.');
            }

            // Clear existing permissions for this role
            RolePermission::where('role', $role)->delete();

            // Add new permissions
            if (!empty($validated['permissions'])) {
                foreach ($validated['permissions'] as $permissionKey => $value) {
                    if ($value) {
                        list($resource, $action) = explode('.', $permissionKey);
                        
                        RolePermission::create([
                            'role' => $role,
                            'permission' => $action,
                            'resource' => $resource,
                            'action' => $action,
                            'allowed' => true,
                        ]);
                    }
                }
            }

            DB::commit();

            return redirect()->route('admin.roles', ['role' => $role])
                ->with('success', "Rechten voor rol '{$role}' succesvol bijgewerkt!");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating role permissions: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden bij het bijwerken van de rol rechten.');
        }
    }

    /**
     * Toggle user active status
     */
    public function toggleActive(User $user)
    {
        $this->checkAdmin();

        try {
            if ($user->id === auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Je kunt je eigen account niet deactiveren.'
                ], 400);
            }

            $user->update(['is_active' => !$user->is_active]);
            
            $status = $user->is_active ? 'geactiveerd' : 'gedeactiveerd';
            
            return response()->json([
                'success' => true,
                'message' => "Gebruiker {$status}!",
                'is_active' => $user->is_active
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling user status: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Er is een fout opgetreden.'
            ], 500);
        }
    }

    /**
     * Bulk update users
     */
    public function bulkUpdate(Request $request)
    {
        $this->checkAdmin();

        $validated = $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'action' => 'required|in:activate,deactivate,change_role,change_company',
            'role' => 'required_if:action,change_role|in:user,account_manager,beheerder',
            'company_id' => 'required_if:action,change_company|exists:companies,id',
        ]);

        try {
            DB::beginTransaction();

            $users = User::whereIn('id', $validated['user_ids'])->get();
            
            foreach ($users as $user) {
                // Skip current user for certain actions
                if ($user->id === auth()->id() && in_array($validated['action'], ['deactivate'])) {
                    continue;
                }

                switch ($validated['action']) {
                    case 'activate':
                        $user->update(['is_active' => true]);
                        break;
                    case 'deactivate':
                        $user->update(['is_active' => false]);
                        break;
                    case 'change_role':
                        if ($user->role !== 'admin') {
                            $user->update(['role' => $validated['role']]);
                        }
                        break;
                    case 'change_company':
                        $user->update(['company_id' => $validated['company_id']]);
                        break;
                }
            }

            DB::commit();

            return redirect()->back()
                ->with('success', count($users) . ' gebruikers succesvol bijgewerkt!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error in bulk update: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden bij het bijwerken.');
        }
    }

    /**
     * Export users with permissions
     */
    public function export(Request $request)
    {
        $this->checkAdmin();

        $users = User::with(['company', 'projectTeams'])->get();
        
        $filename = 'users_permissions_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($users) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID', 'Naam', 'Email', 'Bedrijf', 'Rol', 'Actief',
                'Kan alle projecten zien', 'Kan financiën zien',
                'Aantal projecten', 'Aangemaakt op'
            ]);
            
            // Data
            foreach ($users as $user) {
                fputcsv($file, [
                    $user->id,
                    $user->name,
                    $user->email,
                    $user->company->name ?? '-',
                    ucfirst(str_replace('_', ' ', $user->role)),
                    $user->is_active ? 'Ja' : 'Nee',
                    $user->can_see_all_projects ? 'Ja' : 'Nee',
                    $user->can_see_financial_data ? 'Ja' : 'Nee',
                    $user->projectTeams()->count(),
                    $user->created_at->format('Y-m-d H:i:s'),
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get available permissions organized by category
     */
    private function getAvailablePermissions(): array
    {
        return [
            'projects' => [
                'view_all' => 'Alle projecten bekijken',
                'create' => 'Projecten aanmaken',
                'edit_all' => 'Alle projecten bewerken',
                'delete' => 'Projecten verwijderen',
                'export' => 'Projecten exporteren',
                'manage_team' => 'Project teams beheren',
            ],
            'milestones' => [
                'create' => 'Milestones aanmaken',
                'edit' => 'Milestones bewerken',
                'delete' => 'Milestones verwijderen',
                'manage_budget' => 'Milestone budgetten beheren',
            ],
            'tasks' => [
                'create' => 'Taken aanmaken',
                'edit_all' => 'Alle taken bewerken',
                'delete' => 'Taken verwijderen',
                'assign' => 'Taken toewijzen',
            ],
            'financials' => [
                'view_budgets' => 'Budgetten bekijken',
                'edit_budgets' => 'Budgetten bewerken',
                'view_reports' => 'Financiële rapporten bekijken',
                'export_financial' => 'Financiële data exporteren',
            ],
            'users' => [
                'view' => 'Gebruikers bekijken',
                'create' => 'Gebruikers aanmaken',
                'edit' => 'Gebruikers bewerken',
                'delete' => 'Gebruikers verwijderen',
                'manage_permissions' => 'Rechten beheren',
            ],
            'companies' => [
                'view' => 'Bedrijven bekijken',
                'create' => 'Bedrijven aanmaken',
                'edit' => 'Bedrijven bewerken',
                'delete' => 'Bedrijven verwijderen',
            ],
            'templates' => [
                'view' => 'Templates bekijken',
                'create' => 'Templates aanmaken',
                'edit' => 'Templates bewerken',
                'delete' => 'Templates verwijderen',
                'use' => 'Templates gebruiken',
            ],
        ];
    }

    /**
     * Organize user permissions for display
     */
    private function organizeUserPermissions(User $user): array
    {
        $permissions = $user->permissions ?? [];
        $organized = [];
        
        foreach ($permissions as $key => $value) {
            $parts = explode('.', $key);
            if (count($parts) >= 2) {
                $category = $parts[0];
                $permission = implode('.', array_slice($parts, 1));
                
                if (!isset($organized[$category])) {
                    $organized[$category] = [];
                }
                
                $organized[$category][$permission] = $value;
            }
        }
        
        return $organized;
    }

    /**
     * Update user's custom permissions
     */
    private function updateUserPermissions(User $user, array $permissions): void
    {
        $formattedPermissions = [];
        
        foreach ($permissions as $key => $value) {
            if ($value) {
                // Replace dots in keys with actual dots for nested array
                $key = str_replace('__', '.', $key);
                data_set($formattedPermissions, $key, true);
            }
        }
        
        $user->update(['permissions' => $formattedPermissions]);
    }

    /**
     * Update user's project access
     */
    private function updateProjectAccess(User $user, array $projectIds, array $projectRoles): void
    {
        // Clear existing project team memberships
        $user->projectTeams()->delete();
        
        // Add new project access
        foreach ($projectIds as $projectId) {
            $role = $projectRoles[$projectId] ?? 'team_member';
            
            $project = Project::find($projectId);
            if ($project && method_exists($project, 'addTeamMember')) {
                $project->addTeamMember($user, $role);
            }
        }
    }
}