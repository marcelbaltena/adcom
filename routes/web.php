<?php

use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserHoursController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TaskTemplateController;
use App\Http\Controllers\SubtaskController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\ProjectTemplateController;
use App\Http\Controllers\ServiceTemplateController;
use App\Http\Controllers\MilestoneTemplateController;
use App\Http\Controllers\SubtaskTemplateController;
use App\Http\Controllers\ProjectMilestoneController;
use App\Http\Controllers\ProjectTaskController;
use App\Http\Controllers\ProjectSubtaskController;

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// ========================================
// DASHBOARD - Simplified version
// ========================================
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Test route zonder middleware
Route::get('/test-dashboard', function () {
    return view('dashboard');
})->name('test.dashboard');

// ========================================
// AUTHENTICATED ROUTES - Basic version without custom middleware
// ========================================
Route::middleware(['auth'])->group(function () {
    
    // ========================================
    // ADMIN ROUTES - USER MANAGEMENT (Alles in één)
    // ========================================
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::prefix('users')->name('users.')->group(function () {
            // Basis gebruikersbeheer
            Route::get('/', [UserController::class, 'index'])->name('index');
            Route::get('/create', [UserController::class, 'create'])->name('create');
            Route::post('/', [UserController::class, 'store'])->name('store');
            Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
            Route::put('/{user}', [UserController::class, 'update'])->name('update');
            Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
            
            // Permissions management
            Route::get('/permissions', [UserController::class, 'permissions'])->name('permissions');
            Route::get('/permissions/{user}/edit', [UserController::class, 'editPermissions'])->name('permissions.edit');
            Route::put('/permissions/{user}', [UserController::class, 'updatePermissions'])->name('permissions.update');
            
            // Activity log
            Route::get('/activity-log', [UserController::class, 'activityLog'])->name('activity-log');
            
            // User Hours Management
            Route::prefix('hours')->name('hours.')->group(function () {
                Route::get('/', [UserHoursController::class, 'index'])->name('index');
                Route::get('/{user}/schedule', [UserHoursController::class, 'editSchedule'])->name('edit-schedule');
                Route::put('/{user}/schedule', [UserHoursController::class, 'updateSchedule'])->name('update-schedule');
                Route::get('/{user}/{year}/{month}', [UserHoursController::class, 'editMonthly'])->name('edit-monthly');
                Route::put('/{user}/{year}/{month}', [UserHoursController::class, 'updateMonthly'])->name('update-monthly');
                Route::get('/export', [UserHoursController::class, 'export'])->name('export');
            });
        });
    });
    
    // ========================================
    // PROJECTS - Basic CRUD
    // ========================================
    Route::resource('projects', ProjectController::class);
    
    Route::prefix('projects/{project}')->name('projects.')->group(function () {
        // Milestone Manager View
        Route::get('/milestones', [ProjectController::class, 'milestones'])->name('milestones');
        
        // Budget Overview
        Route::get('/budget-overview', [ProjectController::class, 'budgetOverview'])->name('budget-overview');
        
        // Budget Settings
        Route::post('/update-budget-settings', [ProjectController::class, 'updateBudgetSettings'])->name('update-budget-settings');
    });
    
    // ========================================
    // MILESTONES
    // ========================================
    Route::prefix('milestones')->name('milestones.')->group(function () {
        Route::post('/', [MilestoneController::class, 'store'])->name('store');
        Route::put('/{milestone}', [MilestoneController::class, 'update'])->name('update');
        Route::delete('/{milestone}', [MilestoneController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [MilestoneController::class, 'reorder'])->name('reorder');
        Route::get('/{milestone}/tasks', [MilestoneController::class, 'tasks'])->name('tasks');
        Route::get('/{milestone}/budget-details', [MilestoneController::class, 'getBudgetDetails'])->name('budget-details');
        Route::post('/{milestone}/update-budget', [MilestoneController::class, 'updateBudget'])->name('update-budget-ajax');
    });
    
    // ========================================
    // TASKS
    // ========================================
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::post('/', [TaskController::class, 'store'])->name('store');
        Route::put('/{task}', [TaskController::class, 'update'])->name('update');
        Route::delete('/{task}', [TaskController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [TaskController::class, 'reorder'])->name('reorder');
        Route::post('/{task}/move', [TaskController::class, 'move'])->name('move');
        Route::get('/{task}/budget', [TaskController::class, 'getBudget'])->name('get-budget');
        Route::post('/{task}/budget', [BudgetController::class, 'updateTaskBudget'])->name('update-budget');
    });
    
    // ========================================
    // SUBTASKS
    // ========================================
    Route::prefix('subtasks')->name('subtasks.')->group(function () {
        Route::post('/', [SubtaskController::class, 'store'])->name('store');
        Route::put('/{subtask}', [SubtaskController::class, 'update'])->name('update');
        Route::delete('/{subtask}', [SubtaskController::class, 'destroy'])->name('destroy');
        Route::post('/{subtask}/toggle-status', [SubtaskController::class, 'toggleStatus'])->name('toggle-status');
        Route::post('/reorder', [SubtaskController::class, 'reorder'])->name('reorder');
        Route::post('/{subtask}/move', [SubtaskController::class, 'move'])->name('move');
    });
    
    // ========================================
    // SERVICE TEMPLATES (PRIJSLIJST)
    // ========================================
    Route::prefix('service-templates')->name('service-templates.')->group(function () {
        Route::get('/', [ServiceTemplateController::class, 'index'])->name('index');
        Route::get('/create', [ServiceTemplateController::class, 'create'])->name('create');
        Route::post('/', [ServiceTemplateController::class, 'store'])->name('store');
        Route::get('/{serviceTemplate}', [ServiceTemplateController::class, 'show'])->name('show');
        Route::get('/{serviceTemplate}/edit', [ServiceTemplateController::class, 'edit'])->name('edit');
        Route::put('/{serviceTemplate}', [ServiceTemplateController::class, 'update'])->name('update');
        Route::delete('/{serviceTemplate}', [ServiceTemplateController::class, 'destroy'])->name('destroy');
        Route::get('/{serviceTemplate}/milestones', [ServiceTemplateController::class, 'milestones'])->name('milestones');
        Route::post('/{serviceTemplate}/toggle-active', [ServiceTemplateController::class, 'toggleActive'])->name('toggle-active');
        Route::post('/{serviceTemplate}/clone-to-project', [ServiceTemplateController::class, 'cloneToProject'])->name('clone-to-project');
        Route::get('/ajax/projects', [ServiceTemplateController::class, 'getProjects'])->name('ajax.projects');
    });
    
    // Milestone Templates
    Route::prefix('milestone-templates')->name('milestone-templates.')->group(function () {
        Route::post('/', [MilestoneTemplateController::class, 'store'])->name('store');
        Route::put('/{milestoneTemplate}', [MilestoneTemplateController::class, 'update'])->name('update');
        Route::delete('/{milestoneTemplate}', [MilestoneTemplateController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [MilestoneTemplateController::class, 'reorder'])->name('reorder');
        Route::get('/{milestoneTemplate}/tasks', [MilestoneTemplateController::class, 'tasks'])->name('tasks');
    });
    
    // Task Templates
    Route::prefix('task-templates')->name('task-templates.')->group(function () {
        Route::post('/', [TaskTemplateController::class, 'store'])->name('store');
        Route::put('/{taskTemplate}', [TaskTemplateController::class, 'update'])->name('update');
        Route::delete('/{taskTemplate}', [TaskTemplateController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [TaskTemplateController::class, 'reorder'])->name('reorder');
        Route::get('/{taskTemplate}/subtasks', [TaskTemplateController::class, 'subtasks'])->name('subtasks');
    });
    
    // Subtask Templates
    Route::prefix('subtask-templates')->name('subtask-templates.')->group(function () {
        Route::post('/', [SubtaskTemplateController::class, 'store'])->name('store');
        Route::put('/{subtaskTemplate}', [SubtaskTemplateController::class, 'update'])->name('update');
        Route::delete('/{subtaskTemplate}', [SubtaskTemplateController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [SubtaskTemplateController::class, 'reorder'])->name('reorder');
    });
    
    // ========================================
    // PROJECT TEMPLATES
    // ========================================
    Route::prefix('project-templates')->name('project-templates.')->group(function () {
        Route::get('/', [ProjectTemplateController::class, 'index'])->name('index');
        Route::get('/create', [ProjectTemplateController::class, 'create'])->name('create');
        Route::post('/', [ProjectTemplateController::class, 'store'])->name('store');
        Route::get('/{projectTemplate}/edit', [ProjectTemplateController::class, 'edit'])->name('edit');
        Route::put('/{projectTemplate}', [ProjectTemplateController::class, 'update'])->name('update');
        Route::delete('/{projectTemplate}', [ProjectTemplateController::class, 'destroy'])->name('destroy');
        Route::get('/{projectTemplate}/milestones', [ProjectTemplateController::class, 'milestones'])->name('milestones');
    });
    
    // Project template sub-resources
    Route::prefix('project-milestones')->name('project-milestones.')->group(function () {
        Route::post('/', [ProjectMilestoneController::class, 'store'])->name('store');
        Route::put('/{projectMilestone}', [ProjectMilestoneController::class, 'update'])->name('update');
        Route::delete('/{projectMilestone}', [ProjectMilestoneController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [ProjectMilestoneController::class, 'reorder'])->name('reorder');
    });
    
    Route::prefix('project-tasks')->name('project-tasks.')->group(function () {
        Route::post('/', [ProjectTaskController::class, 'store'])->name('store');
        Route::put('/{projectTask}', [ProjectTaskController::class, 'update'])->name('update');
        Route::delete('/{projectTask}', [ProjectTaskController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [ProjectTaskController::class, 'reorder'])->name('reorder');
    });
    
    Route::prefix('project-subtasks')->name('project-subtasks.')->group(function () {
        Route::post('/', [ProjectSubtaskController::class, 'store'])->name('store');
        Route::put('/{projectSubtask}', [ProjectSubtaskController::class, 'update'])->name('update');
        Route::delete('/{projectSubtask}', [ProjectSubtaskController::class, 'destroy'])->name('destroy');
        Route::post('/reorder', [ProjectSubtaskController::class, 'reorder'])->name('reorder');
    });
    
    // Apply template to project
    Route::get('projects/{project}/apply-template', [ProjectTemplateController::class, 'showApplyForm'])->name('projects.apply-template');
    Route::post('projects/{project}/apply-template', [ProjectTemplateController::class, 'apply'])->name('projects.apply-template.store');
    
    // ========================================
    // COMPANIES
    // ========================================
    Route::resource('companies', CompanyController::class);
    
    Route::prefix('companies/{company}')->name('companies.')->group(function () {
        Route::patch('/toggle-status', [CompanyController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/users', [CompanyController::class, 'users'])->name('users');
        Route::post('/users', [CompanyController::class, 'addUser'])->name('add-user');
        Route::get('/stats', [CompanyController::class, 'stats'])->name('stats');
    });
    
    // ========================================
    // CUSTOMERS
    // ========================================
    Route::resource('customers', CustomerController::class);
    
    Route::prefix('customers/{customer}')->name('customers.')->group(function () {
        Route::patch('/toggle-status', [CustomerController::class, 'toggleStatus'])->name('toggle-status');
        Route::get('/stats', [CustomerController::class, 'stats'])->name('stats');
        Route::get('/create-project', [CustomerController::class, 'createProject'])->name('create-project');
    });
    
    // ========================================
    // USER PROFILE
    // ========================================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';