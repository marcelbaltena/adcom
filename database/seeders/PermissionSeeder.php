<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RolePermission;

class PermissionSeeder extends Seeder
{
    public function run()
    {
        // Clear existing permissions
        RolePermission::truncate();

        // Admin permissions - Admin has access to everything by default in the model
        // We only need to add a few explicit entries for clarity
        RolePermission::grantToRole('admin', '*', '*', '*', 'Volledige toegang tot alles');

        // Beheerder permissions
        $beheerderPermissions = [
            ['permission' => 'manage', 'resource' => 'projects', 'action' => '*', 'description' => 'Kan alle projecten beheren'],
            ['permission' => 'manage', 'resource' => 'milestones', 'action' => '*', 'description' => 'Kan alle milestones beheren'],
            ['permission' => 'manage', 'resource' => 'tasks', 'action' => '*', 'description' => 'Kan alle taken beheren'],
            ['permission' => 'manage', 'resource' => 'customers', 'action' => '*', 'description' => 'Kan alle klanten beheren'],
            ['permission' => 'manage', 'resource' => 'templates', 'action' => '*', 'description' => 'Kan alle templates beheren'],
            ['permission' => 'view', 'resource' => 'financials', 'action' => 'view', 'description' => 'Kan financiële data bekijken'],
            ['permission' => 'view', 'resource' => 'reports', 'action' => 'view', 'description' => 'Kan rapporten bekijken'],
        ];

        foreach ($beheerderPermissions as $perm) {
            RolePermission::grantToRole('beheerder', $perm['permission'], $perm['resource'], $perm['action'], $perm['description']);
        }

        // Account Manager permissions
        $accountManagerPermissions = [
            ['permission' => 'manage', 'resource' => 'projects', 'action' => 'view', 'description' => 'Kan projecten bekijken'],
            ['permission' => 'manage', 'resource' => 'projects', 'action' => 'create', 'description' => 'Kan projecten aanmaken'],
            ['permission' => 'manage', 'resource' => 'projects', 'action' => 'update', 'description' => 'Kan eigen projecten bewerken'],
            ['permission' => 'manage', 'resource' => 'customers', 'action' => '*', 'description' => 'Kan klanten beheren'],
            ['permission' => 'manage', 'resource' => 'milestones', 'action' => 'view', 'description' => 'Kan milestones bekijken'],
            ['permission' => 'manage', 'resource' => 'tasks', 'action' => 'view', 'description' => 'Kan taken bekijken'],
            ['permission' => 'manage', 'resource' => 'tasks', 'action' => 'update', 'description' => 'Kan taken updaten'],
        ];

        foreach ($accountManagerPermissions as $perm) {
            RolePermission::grantToRole('account_manager', $perm['permission'], $perm['resource'], $perm['action'], $perm['description']);
        }

        // User permissions
        $userPermissions = [
            ['permission' => 'view', 'resource' => 'projects', 'action' => 'view', 'description' => 'Kan toegewezen projecten bekijken'],
            ['permission' => 'view', 'resource' => 'milestones', 'action' => 'view', 'description' => 'Kan milestones bekijken'],
            ['permission' => 'view', 'resource' => 'tasks', 'action' => 'view', 'description' => 'Kan taken bekijken'],
            ['permission' => 'update', 'resource' => 'tasks', 'action' => 'update', 'description' => 'Kan taken updaten'],
            ['permission' => 'view', 'resource' => 'subtasks', 'action' => 'view', 'description' => 'Kan subtaken bekijken'],
            ['permission' => 'update', 'resource' => 'subtasks', 'action' => 'update', 'description' => 'Kan subtaken updaten'],
        ];

        foreach ($userPermissions as $perm) {
            RolePermission::grantToRole('user', $perm['permission'], $perm['resource'], $perm['action'], $perm['description']);
        }

        $count = RolePermission::count();
        $this->command->info("Role permissions seeded successfully! Total permissions: {$count}");
        
        // Show summary
        $this->command->info("\nPermissions per role:");
        foreach (RolePermission::getAvailableRoles() as $role) {
            $roleCount = RolePermission::where('role', $role)->where('allowed', true)->count();
            $this->command->info("- {$role}: {$roleCount} permissions");
        }
    }
}