<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Project;
use App\Models\Task;
use App\Models\Subtask;
use App\Models\Company;
use App\Models\Attachment;
use App\Models\Comment;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Model Policy registrations
        Gate::policy(Project::class, \App\Policies\ProjectPolicy::class);
        Gate::policy(Task::class, \App\Policies\TaskPolicy::class);
        Gate::policy(Subtask::class, \App\Policies\SubtaskPolicy::class);
        Gate::policy(Company::class, \App\Policies\CompanyPolicy::class);
        Gate::policy(User::class, \App\Policies\UserPolicy::class);
        Gate::policy(Attachment::class, \App\Policies\AttachmentPolicy::class);
        Gate::policy(Comment::class, \App\Policies\CommentPolicy::class);

        // Implicitly grant "before" ability to admin role
        Gate::before(function ($user, $ability) {
            if ($user->role === 'admin') {
                return true;
            }
        });

        // Define custom gates
        $this->registerGates();
    }

    /**
     * Register custom authorization gates
     */
    private function registerGates(): void
    {
        // User management gates
        Gate::define('manage-users', function (User $user) {
            return $user->canManageUsers();
        });

        Gate::define('manage-companies', function (User $user) {
            return $user->canManageCompanies();
        });

        Gate::define('view-financials', function (User $user) {
            return $user->canViewFinancials();
        });

        Gate::define('manage-permissions', function (User $user) {
            return $user->hasPermission('manage_permissions', 'users');
        });

        // Template gates
        Gate::define('use-templates', function (User $user) {
            return $user->hasPermission('use', 'templates');
        });

        Gate::define('manage-templates', function (User $user) {
            return $user->hasPermission('manage', 'templates') || 
                   $user->hasPermission('create', 'templates') ||
                   $user->hasPermission('edit', 'templates');
        });

        // Project-specific gates
        Gate::define('create-milestone', function (User $user, Project $project) {
            return $user->canEditProject($project) && 
                   $user->hasPermission('create', 'milestones');
        });

        Gate::define('create-task', function (User $user, Project $project) {
            return $user->canEditProject($project) && 
                   $user->hasPermission('create', 'tasks');
        });

        // Team member check
        Gate::define('is-team-member', function (User $user, Project $project) {
            return $project->hasTeamMember($user);
        });

        // Watcher check
        Gate::define('is-watching', function (User $user, $model) {
            if (method_exists($model, 'isWatchedBy')) {
                return $model->isWatchedBy($user);
            }
            return false;
        });

        // Can comment check
        Gate::define('can-comment', function (User $user, $model) {
            if (method_exists($model, 'canUserComment')) {
                return $model->canUserComment($user);
            }
            return false;
        });

        // Financial data access levels
        Gate::define('view-project-budget', function (User $user, Project $project) {
            return $user->canViewProject($project) && $user->canViewFinancials();
        });

        Gate::define('edit-project-budget', function (User $user, Project $project) {
            return $user->canEditProject($project) && 
                   $user->canViewFinancials() && 
                   $user->hasPermission('edit_budgets', 'financials');
        });

        // Report access
        Gate::define('view-reports', function (User $user) {
            return $user->hasPermission('view_reports', 'financials') ||
                   $user->role === 'beheerder' ||
                   $user->role === 'account_manager';
        });

        Gate::define('export-data', function (User $user) {
            return $user->hasPermission('export', 'projects') ||
                   $user->hasPermission('export_financial', 'financials');
        });

        // System settings (only admin)
        Gate::define('manage-settings', function (User $user) {
            return $user->role === 'admin';
        });

        // Activity log viewing
        Gate::define('view-activity-log', function (User $user, $model = null) {
            if ($model && method_exists($model, 'canBeViewedBy')) {
                return $model->canBeViewedBy($user);
            }
            return $user->isManager();
        });

        // Notification preferences
        Gate::define('manage-notifications', function (User $user, User $targetUser) {
            return $user->id === $targetUser->id || $user->role === 'admin';
        });
    }
}
