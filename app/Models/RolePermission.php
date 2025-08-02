<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RolePermission extends Model
{
    use HasFactory;

    protected $fillable = [
        'role',
        'permission',
        'resource',
        'action',
        'allowed',
        'description',
    ];

    protected $casts = [
        'allowed' => 'boolean',
    ];

    /**
     * Check if a role has a specific permission
     */
    public static function roleHasPermission(string $role, string $permission, string $resource, string $action = 'view'): bool
    {
        // Admin has all permissions
        if ($role === 'admin') {
            return true;
        }

        // Check wildcard permissions
        $hasWildcard = self::where('role', $role)
            ->where(function ($query) use ($permission, $resource, $action) {
                $query->where(function ($q) use ($permission, $resource, $action) {
                    $q->where('permission', $permission)
                      ->where('resource', $resource)
                      ->where('action', $action);
                })
                ->orWhere(function ($q) use ($permission, $resource) {
                    $q->where('permission', $permission)
                      ->where('resource', $resource)
                      ->where('action', '*');
                })
                ->orWhere(function ($q) use ($permission) {
                    $q->where('permission', $permission)
                      ->where('resource', '*')
                      ->where('action', '*');
                })
                ->orWhere(function ($q) {
                    $q->where('permission', '*')
                      ->where('resource', '*')
                      ->where('action', '*');
                });
            })
            ->where('allowed', true)
            ->exists();

        return $hasWildcard;
    }

    /**
     * Get all permissions for a role
     */
    public static function getPermissionsForRole(string $role): array
    {
        return self::where('role', $role)
            ->where('allowed', true)
            ->get()
            ->map(function ($permission) {
                return [
                    'permission' => $permission->permission,
                    'resource' => $permission->resource,
                    'action' => $permission->action,
                    'description' => $permission->description,
                ];
            })
            ->toArray();
    }

    /**
     * Grant a permission to a role
     */
    public static function grantToRole(string $role, string $permission, string $resource, string $action = 'view', string $description = null): void
    {
        self::updateOrCreate(
            [
                'role' => $role,
                'permission' => $permission,
                'resource' => $resource,
                'action' => $action,
            ],
            [
                'allowed' => true,
                'description' => $description,
            ]
        );
    }

    /**
     * Revoke a permission from a role
     */
    public static function revokeFromRole(string $role, string $permission, string $resource, string $action = 'view'): void
    {
        self::where('role', $role)
            ->where('permission', $permission)
            ->where('resource', $resource)
            ->where('action', $action)
            ->update(['allowed' => false]);
    }

    /**
     * Get available roles
     */
    public static function getAvailableRoles(): array
    {
        return ['admin', 'beheerder', 'account_manager', 'user'];
    }

    /**
     * Get available resources
     */
    public static function getAvailableResources(): array
    {
        return [
            'projects' => 'Projecten',
            'milestones' => 'Milestones',
            'tasks' => 'Taken',
            'subtasks' => 'Subtaken',
            'users' => 'Gebruikers',
            'companies' => 'Bedrijven',
            'customers' => 'Klanten',
            'financials' => 'Financiën',
            'reports' => 'Rapporten',
            'templates' => 'Templates',
            'settings' => 'Instellingen',
        ];
    }

    /**
     * Get available actions
     */
    public static function getAvailableActions(): array
    {
        return [
            'view' => 'Bekijken',
            'create' => 'Aanmaken',
            'update' => 'Bewerken',
            'delete' => 'Verwijderen',
            'manage' => 'Beheren',
        ];
    }

    /**
     * Scope by role
     */
    public function scopeForRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope allowed permissions
     */
    public function scopeAllowed($query)
    {
        return $query->where('allowed', true);
    }

    /**
     * Scope by resource
     */
    public function scopeForResource($query, string $resource)
    {
        return $query->where('resource', $resource);
    }
}