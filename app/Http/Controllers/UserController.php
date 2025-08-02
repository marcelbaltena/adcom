<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Company;
use App\Models\Project;
use App\Models\RolePermission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /**
     * Check if user is admin or beheerder
     */
    private function checkAccess()
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'beheerder'])) {
            abort(403, 'Geen toegang - alleen voor administrators en beheerders');
        }
    }

    /**
     * Display a listing of users
     */
    public function index(Request $request)
    {
        $this->checkAccess();
        
        $query = User::with(['company', 'projects']);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Company filter
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        // Role filter
        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        // Status filter
        if ($request->filled('status')) {
            $isActive = $request->input('status') === 'active';
            $query->where('is_active', $isActive);
        }

        $users = $query->orderBy('name')->paginate(20);
        $companies = Company::orderBy('name')->get();

        return view('admin.users.index', compact('users', 'companies'));
    }

    /**
     * Show the form for creating a new user
     */
    public function create()
    {
        $this->checkAccess();
        
        $companies = Company::orderBy('name')->get();
        $projects = Project::orderBy('name')->get();
        
        return view('admin.users.create', compact('companies', 'projects'));
    }

    /**
     * Store a newly created user
     */
    public function store(Request $request)
    {
        $this->checkAccess();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'company_id' => 'required|exists:companies,id',
            'role' => 'required|in:admin,beheerder,account_manager,user',
            'is_active' => 'boolean',
            'project_ids' => 'array',
            'project_ids.*' => 'exists:projects,id'
        ]);

        DB::beginTransaction();
        try {
            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'company_id' => $validated['company_id'],
                'role' => $validated['role'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Assign to projects - FIX voor polymorphic relatie
            if (!empty($validated['project_ids'])) {
                foreach ($validated['project_ids'] as $projectId) {
                    DB::table('assignees')->insert([
                        'assignable_type' => 'App\Models\Project',
                        'assignable_id' => $projectId,
                        'user_id' => $user->id,
                        'role' => 'assignee',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // Log activity
            $this->logActivity('created', $user, [
                'user_name' => $user->name,
                'role' => $user->role
            ]);

            DB::commit();
            return redirect()->route('admin.users.index')
                ->with('success', 'Gebruiker succesvol aangemaakt');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating user: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Er is een fout opgetreden bij het aanmaken van de gebruiker');
        }
    }

    /**
     * Show the form for editing the user
     */
    public function edit(User $user)
    {
        $this->checkAccess();
        
        $companies = Company::orderBy('name')->get();
        $projects = Project::orderBy('name')->get();
        $userProjectIds = DB::table('assignees')
            ->where('user_id', $user->id)
            ->where('assignable_type', 'App\Models\Project')
            ->pluck('assignable_id')
            ->toArray();
        
        return view('admin.users.edit', compact('user', 'companies', 'projects', 'userProjectIds'));
    }

    /**
     * Update the user
     */
    public function update(Request $request, User $user)
    {
        $this->checkAccess();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'company_id' => 'required|exists:companies,id',
            'role' => 'required|in:admin,beheerder,account_manager,user',
            'is_active' => 'boolean',
            'project_ids' => 'array',
            'project_ids.*' => 'exists:projects,id'
        ]);

        DB::beginTransaction();
        try {
            $oldValues = $user->toArray();

            // Update user
            $user->update([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'company_id' => $validated['company_id'],
                'role' => $validated['role'],
                'is_active' => $validated['is_active'] ?? true,
            ]);

            // Update password if provided
            if (!empty($validated['password'])) {
                $user->update(['password' => Hash::make($validated['password'])]);
            }

            // Update project assignments - FIX voor polymorphic relatie
            DB::table('assignees')
                ->where('user_id', $user->id)
                ->where('assignable_type', 'App\Models\Project')
                ->delete();
                
            if (!empty($validated['project_ids'])) {
                foreach ($validated['project_ids'] as $projectId) {
                    DB::table('assignees')->insert([
                        'assignable_type' => 'App\Models\Project',
                        'assignable_id' => $projectId,
                        'user_id' => $user->id,
                        'role' => 'assignee',
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // Log activity
            $this->logActivity('updated', $user, [
                'old_values' => $oldValues,
                'new_values' => $user->fresh()->toArray()
            ]);

            DB::commit();
            return redirect()->route('admin.users.index')
                ->with('success', 'Gebruiker succesvol bijgewerkt');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating user: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Er is een fout opgetreden bij het bijwerken van de gebruiker');
        }
    }

    /**
     * Display user permissions
     */
    public function permissions(Request $request)
    {
        $this->checkAccess();
        
        $query = User::with(['company']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->input('company_id'));
        }

        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        $users = $query->orderBy('name')->paginate(20);
        $companies = Company::orderBy('name')->get();

        // Get permissions for each user
        foreach ($users as $user) {
            $user->permissions = $this->getUserPermissions($user);
        }

        return view('admin.users.permissions', compact('users', 'companies'));
    }

    /**
     * Show the form for editing user permissions
     */
    public function editPermissions(User $user)
    {
        $this->checkAccess();
        
        $companies = Company::orderBy('name')->get();
        
        // FIX: Gebruik created_by_company_id in plaats van company_id
        $projects = Project::where('created_by_company_id', $user->company_id)
                          ->orderBy('name')
                          ->get();
        
        // Get current permissions
        $userPermissions = $this->getUserPermissions($user);
        
        // Get all available permissions grouped by category
        $permissionCategories = $this->getPermissionCategories();
        
        // Get user's project access - FIX voor polymorphic relatie
        $projectAccess = DB::table('assignees')
            ->where('user_id', $user->id)
            ->where('assignable_type', 'App\Models\Project')
            ->pluck('role', 'assignable_id')
            ->toArray();

        return view('admin.users.edit-permissions', compact(
            'user', 
            'companies', 
            'projects', 
            'userPermissions', 
            'permissionCategories',
            'projectAccess'
        ));
    }

    /**
     * Update user permissions
     */
    public function updatePermissions(Request $request, User $user)
    {
        $this->checkAccess();
        
        $validated = $request->validate([
            'role' => 'required|in:admin,beheerder,account_manager,user',
            'permissions' => 'array',
            'permissions.*' => 'string',
            'project_access' => 'array',
            'project_access.*' => 'in:view,edit,full'
        ]);

        DB::beginTransaction();
        try {
            // Update user role
            $user->update(['role' => $validated['role']]);

            // Clear existing custom permissions
            RolePermission::where('role', $user->id)->delete();

            // Add custom permissions if not admin
            if ($validated['role'] !== 'admin' && !empty($validated['permissions'])) {
                foreach ($validated['permissions'] as $permission) {
                    list($resource, $action) = explode('.', $permission);
                    RolePermission::create([
                        'role' => $user->id,
                        'permission' => $permission,
                        'resource' => $resource,
                        'action' => $action,
                        'allowed' => true
                    ]);
                }
            }

            // Update project access - FIX voor polymorphic relatie
            DB::table('assignees')
                ->where('user_id', $user->id)
                ->where('assignable_type', 'App\Models\Project')
                ->delete();
                
            if (!empty($validated['project_access'])) {
                foreach ($validated['project_access'] as $projectId => $role) {
                    // Valideer dat de role een geldige waarde is
                    $validRole = in_array($role, ['owner', 'assignee', 'reviewer']) ? $role : 'assignee';
                    
                    DB::table('assignees')->insert([
                        'assignable_type' => 'App\Models\Project',
                        'assignable_id' => $projectId,
                        'user_id' => $user->id,
                        'role' => $validRole,
                        'created_at' => now(),
                        'updated_at' => now()
                    ]);
                }
            }

            // Log activity
            $this->logActivity('permissions_updated', $user, [
                'role' => $validated['role'],
                'permissions_count' => count($validated['permissions'] ?? [])
            ]);

            DB::commit();
            return redirect()->route('admin.users.permissions')
                ->with('success', 'Gebruikersrechten succesvol bijgewerkt');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating permissions: ' . $e->getMessage());
            return back()->withInput()
                ->with('error', 'Er is een fout opgetreden bij het bijwerken van de rechten');
        }
    }

    /**
     * Remove the user
     */
    public function destroy(User $user)
    {
        $this->checkAccess();
        
        try {
            // Log activity before deletion
            $this->logActivity('deleted', $user, [
                'user_name' => $user->name,
                'user_email' => $user->email
            ]);

            $user->delete();

            return redirect()->route('admin.users.index')
                ->with('success', 'Gebruiker succesvol verwijderd');
        } catch (\Exception $e) {
            Log::error('Error deleting user: ' . $e->getMessage());
            return back()->with('error', 'Er is een fout opgetreden bij het verwijderen van de gebruiker');
        }
    }

    /**
     * Show activity log for users
     */
    public function activityLog(Request $request)
    {
        $this->checkAccess();
        
        $query = DB::table('activity_logs')
            ->join('users', 'activity_logs.user_id', '=', 'users.id')
            ->where('loggable_type', 'App\Models\User')
            ->select(
                'activity_logs.*',
                'users.name as performed_by'
            );

        // Date filter
        if ($request->filled('date_from')) {
            $query->where('activity_logs.created_at', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->where('activity_logs.created_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        // User filter
        if ($request->filled('user_id')) {
            $query->where('activity_logs.user_id', $request->input('user_id'));
        }

        // Action filter
        if ($request->filled('action')) {
            $query->where('activity_logs.action', $request->input('action'));
        }

        $activities = $query->orderBy('activity_logs.created_at', 'desc')->paginate(50);
        
        // Get users for filter
        $users = User::orderBy('name')->get();

        return view('admin.users.activity-log', compact('activities', 'users'));
    }

    /**
     * Get user permissions
     */
    private function getUserPermissions($user)
    {
        // Admin has all permissions
        if ($user->role === 'admin') {
            return ['*'];
        }

        // Get role-based permissions
        $permissions = RolePermission::where('role', $user->role)
            ->where('allowed', true)
            ->pluck('permission')
            ->toArray();

        // Get user-specific permissions
        $userPermissions = RolePermission::where('role', $user->id)
            ->where('allowed', true)
            ->pluck('permission')
            ->toArray();

        return array_unique(array_merge($permissions, $userPermissions));
    }

    /**
     * Get permission categories
     */
    private function getPermissionCategories()
    {
        return [
            'Projecten' => [
                'projects.view' => 'Projecten bekijken',
                'projects.create' => 'Projecten aanmaken',
                'projects.edit' => 'Projecten bewerken',
                'projects.delete' => 'Projecten verwijderen',
                'projects.view_all' => 'Alle projecten bekijken',
                'projects.manage_team' => 'Projectteams beheren',
                'projects.view_financials' => 'Financiële gegevens bekijken'
            ],
            'Klanten' => [
                'customers.view' => 'Klanten bekijken',
                'customers.create' => 'Klanten aanmaken',
                'customers.edit' => 'Klanten bewerken',
                'customers.delete' => 'Klanten verwijderen',
                'customers.view_all' => 'Alle klanten bekijken'
            ],
            'Gebruikers' => [
                'users.view' => 'Gebruikers bekijken',
                'users.create' => 'Gebruikers aanmaken',
                'users.edit' => 'Gebruikers bewerken',
                'users.delete' => 'Gebruikers verwijderen',
                'users.manage_permissions' => 'Rechten beheren'
            ],
            'Bedrijven' => [
                'companies.view' => 'Bedrijf bekijken',
                'companies.edit' => 'Bedrijf bewerken',
                'companies.manage_settings' => 'Bedrijfsinstellingen beheren'
            ],
            'Rapportages' => [
                'reports.view' => 'Rapportages bekijken',
                'reports.export' => 'Rapportages exporteren',
                'reports.financial' => 'Financiële rapportages'
            ],
            'Templates' => [
                'templates.view' => 'Templates bekijken',
                'templates.create' => 'Templates aanmaken',
                'templates.edit' => 'Templates bewerken',
                'templates.delete' => 'Templates verwijderen'
            ]
        ];
    }

    /**
     * Log activity
     */
    private function logActivity($action, $user, $properties = [])
    {
        DB::table('activity_logs')->insert([
            'loggable_type' => 'App\Models\User',
            'loggable_id' => $user->id,
            'user_id' => auth()->id(),
            'action' => $action,
            'old_values' => json_encode($properties['old_values'] ?? null),
            'new_values' => json_encode($properties['new_values'] ?? null),
            'description' => $this->getActivityDescription($action, $properties),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Get activity description
     */
    private function getActivityDescription($action, $properties)
    {
        switch ($action) {
            case 'created':
                return "Gebruiker {$properties['user_name']} aangemaakt met rol {$properties['role']}";
            case 'updated':
                return "Gebruiker bijgewerkt";
            case 'deleted':
                return "Gebruiker {$properties['user_name']} ({$properties['user_email']}) verwijderd";
            case 'permissions_updated':
                return "Rechten bijgewerkt - Rol: {$properties['role']}, Permissions: {$properties['permissions_count']}";
            default:
                return "Actie: {$action}";
        }
    }
}