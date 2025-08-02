<?php

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ProjectPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true; // Everyone can see project list (filtered in controller)
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Project $project): bool
    {
        return $user->canViewProject($project);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create', 'projects');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Project $project): bool
    {
        return $user->canEditProject($project);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Project $project): bool
    {
        // Only admins can delete projects
        if ($user->role === 'admin') {
            return true;
        }

        // Beheerder can delete projects from their company
        if ($user->role === 'beheerder' && $user->company_id) {
            return in_array($user->company_id, [
                $project->billing_company_id,
                $project->created_by_company_id
            ]);
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Project $project): bool
    {
        return $this->delete($user, $project);
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Project $project): bool
    {
        return $user->role === 'admin';
    }

    /**
     * Determine whether the user can view the project team.
     */
    public function viewTeam(User $user, Project $project): bool
    {
        return $user->canViewProject($project);
    }

    /**
     * Determine whether the user can manage the project team.
     */
    public function manageTeam(User $user, Project $project): bool
    {
        // Admins can always manage teams
        if ($user->role === 'admin') {
            return true;
        }

        // Project managers can manage their project teams
        if ($project->isProjectManager($user)) {
            return true;
        }

        // Project owner can manage team
        if ($project->user_id === $user->id) {
            return true;
        }

        // Check custom permission
        return $user->hasPermission('manage_team', 'projects');
    }

    /**
     * Determine whether the user can view project financials.
     */
    public function viewFinancials(User $user, Project $project): bool
    {
        // Must be able to view project first
        if (!$user->canViewProject($project)) {
            return false;
        }

        // Then check financial permissions
        return $user->canViewFinancials();
    }

    /**
     * Determine whether the user can edit project budget.
     */
    public function editBudget(User $user, Project $project): bool
    {
        // Must be able to edit project
        if (!$user->canEditProject($project)) {
            return false;
        }

        // And have financial permissions
        return $user->canViewFinancials() && $user->hasPermission('edit_budgets', 'financials');
    }

    /**
     * Determine whether the user can export the project.
     */
    public function export(User $user, Project $project): bool
    {
        return $user->canViewProject($project) && $user->hasPermission('export', 'projects');
    }
}

// ============================================
// TaskPolicy
// app/Policies/TaskPolicy.php
// ============================================

namespace App\Policies;

use App\Models\Task;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TaskPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Task $task): bool
    {
        return $task->canBeViewedBy($user);
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->hasPermission('create', 'tasks');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Task $task): bool
    {
        return $task->canBeEditedBy($user);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Task $task): bool
    {
        // Check if user can edit the parent project
        if ($user->canEditProject($task->project)) {
            return true;
        }

        // Check specific permission
        return $user->hasPermission('delete', 'tasks');
    }

    /**
     * Determine whether the user can assign the task.
     */
    public function assign(User $user, Task $task): bool
    {
        // Project managers and above can assign
        if ($task->project->isProjectManager($user)) {
            return true;
        }

        // Check specific permission
        return $user->hasPermission('assign', 'tasks');
    }

    /**
     * Determine whether the user can add comments.
     */
    public function comment(User $user, Task $task): bool
    {
        return $task->canBeViewedBy($user) && $task->allow_comments;
    }

    /**
     * Determine whether the user can add attachments.
     */
    public function attach(User $user, Task $task): bool
    {
        return $task->canBeEditedBy($user);
    }
}

// ============================================
// SubtaskPolicy
// app/Policies/SubtaskPolicy.php
// ============================================

namespace App\Policies;

use App\Models\Subtask;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SubtaskPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Subtask $subtask): bool
    {
        return $subtask->canBeViewedBy($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create', 'tasks');
    }

    public function update(User $user, Subtask $subtask): bool
    {
        return $subtask->canBeEditedBy($user);
    }

    public function delete(User $user, Subtask $subtask): bool
    {
        // Check parent task permissions
        if ($subtask->task && $user->can('delete', $subtask->task)) {
            return true;
        }

        return $user->hasPermission('delete', 'tasks');
    }

    public function toggle(User $user, Subtask $subtask): bool
    {
        // Users assigned to the subtask can toggle completion
        if ($subtask->isAssignedTo($user)) {
            return true;
        }

        return $subtask->canBeEditedBy($user);
    }
}

// ============================================
// CompanyPolicy
// app/Policies/CompanyPolicy.php
// ============================================

namespace App\Policies;

use App\Models\Company;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CompanyPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view', 'companies');
    }

    public function view(User $user, Company $company): bool
    {
        // Users can view their own company
        if ($user->company_id === $company->id) {
            return true;
        }

        return $user->hasPermission('view', 'companies');
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create', 'companies');
    }

    public function update(User $user, Company $company): bool
    {
        // Beheerder can edit their own company
        if ($user->role === 'beheerder' && $user->company_id === $company->id) {
            return true;
        }

        return $user->hasPermission('edit', 'companies');
    }

    public function delete(User $user, Company $company): bool
    {
        return $user->hasPermission('delete', 'companies');
    }

    public function manageUsers(User $user, Company $company): bool
    {
        // Beheerder can manage users in their own company
        if ($user->role === 'beheerder' && $user->company_id === $company->id) {
            return true;
        }

        return $user->canManageUsers();
    }
}

// ============================================
// UserPolicy
// app/Policies/UserPolicy.php
// ============================================

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class UserPolicy
{
    use HandlesAuthorization;

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('view', 'users');
    }

    public function view(User $user, User $model): bool
    {
        // Users can view their own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Users in same company can view each other (if permission allows)
        if ($user->isSameCompany($model)) {
            return $user->hasPermission('view', 'users');
        }

        return $user->canManageUsers();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('create', 'users');
    }

    public function update(User $user, User $model): bool
    {
        // Users can update their own profile (limited fields)
        if ($user->id === $model->id) {
            return true;
        }

        // Beheerder can only edit users in their company
        if ($user->role === 'beheerder' && !$user->isSameCompany($model)) {
            return false;
        }

        return $user->hasPermission('edit', 'users');
    }

    public function delete(User $user, User $model): bool
    {
        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        // Beheerder can only delete users in their company
        if ($user->role === 'beheerder' && !$user->isSameCompany($model)) {
            return false;
        }

        return $user->hasPermission('delete', 'users');
    }

    public function managePermissions(User $user, User $model): bool
    {
        // Cannot manage own permissions
        if ($user->id === $model->id) {
            return false;
        }

        // Cannot manage admin permissions unless you're admin
        if ($model->role === 'admin' && $user->role !== 'admin') {
            return false;
        }

        return $user->hasPermission('manage_permissions', 'users');
    }

    public function impersonate(User $user, User $model): bool
    {
        // Only admins can impersonate
        if ($user->role !== 'admin') {
            return false;
        }

        // Cannot impersonate another admin
        if ($model->role === 'admin') {
            return false;
        }

        return true;
    }
}

// ============================================
// AttachmentPolicy
// app/Policies/AttachmentPolicy.php
// ============================================

namespace App\Policies;

use App\Models\Attachment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AttachmentPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Attachment $attachment): bool
    {
        return $attachment->canBeDownloadedBy($user);
    }

    public function download(User $user, Attachment $attachment): bool
    {
        return $attachment->canBeDownloadedBy($user);
    }

    public function delete(User $user, Attachment $attachment): bool
    {
        // Owner can delete their own attachments
        if ($attachment->user_id === $user->id) {
            return true;
        }

        // Check if user can edit the parent model
        if ($attachment->attachable) {
            if (method_exists($attachment->attachable, 'canBeEditedBy')) {
                return $attachment->attachable->canBeEditedBy($user);
            }
        }

        return $user->role === 'admin';
    }
}

// ============================================
// CommentPolicy
// app/Policies/CommentPolicy.php
// ============================================

namespace App\Policies;

use App\Models\Comment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommentPolicy
{
    use HandlesAuthorization;

    public function create(User $user): bool
    {
        return true; // Check is done on the parent model
    }

    public function update(User $user, Comment $comment): bool
    {
        return $comment->canBeEditedBy($user);
    }

    public function delete(User $user, Comment $comment): bool
    {
        return $comment->canBeDeletedBy($user);
    }

    public function viewInternal(User $user): bool
    {
        return $user->isManager();
    }
}