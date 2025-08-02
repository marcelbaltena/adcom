<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Customer;
use App\Models\Company;
use App\Models\User;
use App\Models\ProjectTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Carbon\Carbon;

class ProjectController extends Controller
{
    /**
     * Display a listing of projects with filtering and pagination
     */
    public function index(Request $request)
    {
        $query = Project::with([
            'customer', 
            'billingCompany', 
            'createdByCompany', 
            'user', 
            'milestones.tasks'
        ]);

        // Apply search filter
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        // Apply customer filter
        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        // Apply company filter (billing or created by)
        if ($request->filled('company_id')) {
            $query->where(function($q) use ($request) {
                $q->where('billing_company_id', $request->company_id)
                  ->orWhere('created_by_company_id', $request->company_id);
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply budget status filter
        if ($request->filled('budget_status')) {
            $query->where('budget_status', $request->budget_status);
        }

        // Apply user filter (if provided)
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        // Order by most recently updated
        $projects = $query->orderBy('updated_at', 'desc')->paginate(12);

        // Get filter options for dropdowns
        $customers = Customer::orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('projects.index', compact('projects', 'customers', 'companies', 'users'));
    }

    /**
     * Show the form for creating a new project
     */
    public function create()
    {
        $customers = Customer::with('company')->orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('projects.create', compact('customers', 'companies', 'users'));
    }

    /**
     * Store a newly created project in storage WITH PROJECT TEMPLATE SUPPORT
     */
    public function store(Request $request)
    {
        // ===== START DETAILED LOGGING =====
        Log::info('========================================');
        Log::info('=== PROJECT CREATE ATTEMPT STARTED ===');
        Log::info('========================================');
        Log::info('Timestamp: ' . now()->format('Y-m-d H:i:s'));
        Log::info('User ID: ' . Auth::id());
        Log::info('User Name: ' . (Auth::check() ? Auth::user()->name : 'Not logged in'));
        
        // Log ALL request data
        Log::info('RAW Request Data:', $request->all());
        
        // Log template specific fields
        Log::info('Template Fields:', [
            'project_template_id' => $request->project_template_id,
            'template_start_date' => $request->template_start_date,
        ]);

        try {
            // Start validation
            Log::info('--- Starting Validation ---');
            
            $validated = $request->validate([
                'name' => 'required|string|max:500',
                'description' => 'nullable|string',
                'status' => 'required|in:planning,active,on_hold,completed,cancelled',
                'currency' => 'required|string|max:10',
                'budget' => 'required|numeric|min:0',
                'project_value' => 'nullable|numeric|min:0',
                'customer_id' => 'required|exists:customers,id',
                'billing_company_id' => 'required|exists:companies,id',
                'created_by_company_id' => 'nullable|exists:companies,id',
                'user_id' => 'required|exists:users,id',
                'source' => 'required|in:direct,referral,marketing,existing_customer',
                'customer_can_view' => 'boolean',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'budget_tolerance_percentage' => 'nullable|numeric|min:0|max:100',
                'budget_warning_percentage' => 'nullable|numeric|min:0|max:100',
                // Template fields
                'project_template_id' => 'nullable|exists:project_templates,id',
                'template_start_date' => 'required_if:project_template_id,!=,null|date'
            ]);
            
            Log::info('✓ Validation PASSED');
            Log::info('Validated data:', $validated);

            // Start database transaction
            Log::info('--- Starting Database Transaction ---');
            DB::beginTransaction();

            // Prepare project data
            $projectData = [
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
                'currency' => $request->currency,
                'budget' => $request->budget,
                'project_value' => $request->project_value ?? $request->budget,
                'customer_id' => $request->customer_id,
                'billing_company_id' => $request->billing_company_id,
                'created_by_company_id' => $request->created_by_company_id ?? $request->billing_company_id,
                'user_id' => $request->user_id,
                'source' => $request->source ?? 'direct',
                'customer_can_view' => $request->boolean('customer_can_view', true),
                'customer_permissions' => $request->customer_permissions ? json_encode($request->customer_permissions) : json_encode([]),
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'budget_tolerance_percentage' => $request->budget_tolerance_percentage ?? 10.00,
                'budget_warning_percentage' => $request->budget_warning_percentage ?? 5.00,
                'spent' => 0.00,
                'allocated_budget' => 0.00,
                'budget_status' => 'on_track',
            ];
            
            Log::info('Project data prepared for insertion:', $projectData);

            // Check if model is fillable
            Log::info('Checking Project model fillable attributes...');
            $fillable = (new Project())->getFillable();
            Log::info('Fillable attributes:', $fillable);
            
            // Check for missing fillable fields
            $missingFillable = array_diff(array_keys($projectData), $fillable);
            if (!empty($missingFillable)) {
                Log::warning('WARNING: These fields are NOT in fillable array:', $missingFillable);
            }

            // Attempt to create project
            Log::info('--- Attempting to Create Project ---');
            $project = Project::create($projectData);
            
            Log::info('✓ Project created successfully!');
            Log::info('Project ID: ' . $project->id);
            Log::info('Project details:', $project->toArray());

            // Apply template if selected
            if (!empty($validated['project_template_id'])) {
                Log::info('--- Applying Project Template ---');
                Log::info('Template ID: ' . $validated['project_template_id']);
                Log::info('Template Start Date: ' . $validated['template_start_date']);
                
                $template = ProjectTemplate::with(['milestones.tasks.subtasks'])
                    ->findOrFail($validated['project_template_id']);
                    
                Log::info('Template found: ' . $template->name);
                Log::info('Template has ' . $template->milestones->count() . ' milestones');
                
                $startDate = Carbon::parse($validated['template_start_date']);
                
                // Clone template to project
                $template->cloneToProject($project, $startDate);
                
                Log::info('✓ Template applied successfully');
            }

            // Initialize budget calculations
            Log::info('--- Running Budget Calculations ---');
            
            // Check if recalculateBudget method exists
            if (method_exists($project, 'recalculateBudget')) {
                $project->recalculateBudget();
                Log::info('✓ Budget calculations completed');
            } else {
                Log::warning('WARNING: recalculateBudget method not found on Project model');
            }

            // Commit transaction
            Log::info('--- Committing Database Transaction ---');
            DB::commit();
            Log::info('✓ Transaction committed successfully');

            Log::info('========================================');
            Log::info('=== PROJECT CREATED SUCCESSFULLY! ===');
            Log::info('========================================');

            // Redirect based on whether template was used
            if (!empty($validated['project_template_id'])) {
                return redirect()->route('projects.milestones', $project)
                    ->with('success', sprintf(
                        'Project created successfully with template "%s"! %d milestones have been added.',
                        $template->name,
                        $template->milestones->count()
                    ));
            }

            return redirect()->route('projects.show', $project)
                           ->with('success', 'Project created successfully!');

        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('========================================');
            Log::error('=== VALIDATION FAILED ===');
            Log::error('========================================');
            Log::error('Validation errors:', $e->errors());
            Log::error('Failed fields:', array_keys($e->errors()));
            throw $e;
            
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollback();
            Log::error('========================================');
            Log::error('=== DATABASE ERROR ===');
            Log::error('========================================');
            Log::error('SQL Error Code: ' . $e->getCode());
            Log::error('SQL Error Message: ' . $e->getMessage());
            Log::error('SQL: ' . $e->getSql() ?? 'N/A');
            Log::error('Bindings:', $e->getBindings() ?? []);
            
            // More user-friendly error message
            $userMessage = 'Database error occurred. ';
            if (strpos($e->getMessage(), 'cannot be null') !== false) {
                $userMessage .= 'A required field is missing.';
            } elseif (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                $userMessage .= 'A project with similar details already exists.';
            } else {
                $userMessage .= 'Please check your input and try again.';
            }
            
            return back()->withInput()
                        ->withErrors(['error' => $userMessage . ' (Error: ' . $e->getCode() . ')']);
                        
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('========================================');
            Log::error('=== UNEXPECTED ERROR ===');
            Log::error('========================================');
            Log::error('Error Type: ' . get_class($e));
            Log::error('Error Message: ' . $e->getMessage());
            Log::error('Error Code: ' . $e->getCode());
            Log::error('File: ' . $e->getFile());
            Log::error('Line: ' . $e->getLine());
            Log::error('Stack trace:');
            Log::error($e->getTraceAsString());
            
            return back()->withInput()
                        ->withErrors(['error' => 'An unexpected error occurred: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified project
     */
    public function show(Project $project)
    {
        $project->load([
            'customer', 
            'billingCompany', 
            'createdByCompany', 
            'user',
            'milestones' => function($query) {
                $query->with(['tasks'])->orderBy('order')->orderBy('created_at');
            }
        ]);

        // Update budget calculations
        $project->recalculateBudget();

        return view('projects.show', compact('project'));
    }

    /**
     * Show the form for editing the specified project
     */
    public function edit(Project $project)
    {
        $customers = Customer::with('company')->orderBy('name')->get();
        $companies = Company::orderBy('name')->get();
        $users = User::orderBy('name')->get();

        return view('projects.edit', compact('project', 'customers', 'companies', 'users'));
    }

    /**
     * Update the specified project in storage
     */
    public function update(Request $request, Project $project)
    {
        Log::info('=== PROJECT UPDATE ATTEMPT ===');
        Log::info('Project ID: ' . $project->id);
        Log::info('Request data:', $request->all());

        $request->validate([
            'name' => 'required|string|max:500',
            'description' => 'nullable|string',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
            'currency' => 'required|string|max:10',
            'budget' => 'required|numeric|min:0',
            'project_value' => 'nullable|numeric|min:0',
            'customer_id' => 'required|exists:customers,id',
            'billing_company_id' => 'required|exists:companies,id',
            'created_by_company_id' => 'nullable|exists:companies,id',
            'user_id' => 'required|exists:users,id',
            'source' => 'required|in:direct,referral,marketing,existing_customer',
            'customer_can_view' => 'boolean',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'budget_tolerance_percentage' => 'nullable|numeric|min:0|max:100',
            'budget_warning_percentage' => 'nullable|numeric|min:0|max:100',
        ]);

        try {
            DB::beginTransaction();

            $project->update([
                'name' => $request->name,
                'description' => $request->description,
                'status' => $request->status,
                'currency' => $request->currency,
                'budget' => $request->budget,
                'project_value' => $request->project_value,
                'customer_id' => $request->customer_id,
                'billing_company_id' => $request->billing_company_id,
                'created_by_company_id' => $request->created_by_company_id,
                'user_id' => $request->user_id,
                'source' => $request->source,
                'customer_can_view' => $request->boolean('customer_can_view'),
                'customer_permissions' => $request->customer_permissions ? json_encode($request->customer_permissions) : null,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'budget_tolerance_percentage' => $request->budget_tolerance_percentage,
                'budget_warning_percentage' => $request->budget_warning_percentage,
            ]);

            // Recalculate budget after update
            $project->recalculateBudget();

            DB::commit();
            
            Log::info('✓ Project updated successfully');

            return redirect()->route('projects.show', $project)
                           ->with('success', 'Project updated successfully!');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error updating project: ' . $e->getMessage());
            return back()->withInput()
                        ->withErrors(['error' => 'Failed to update project. Please try again.']);
        }
    }

    /**
     * Remove the specified project from storage
     */
    public function destroy(Project $project)
    {
        try {
            DB::beginTransaction();

            // Check if project has milestones/tasks
            if ($project->milestones()->count() > 0) {
                return back()->withErrors(['error' => 'Cannot delete project with existing milestones. Please remove milestones first.']);
            }

            // Check if project has time entries
            if (method_exists($project, 'timeEntries') && $project->timeEntries()->count() > 0) {
                return back()->withErrors(['error' => 'Cannot delete project with existing time entries. Please remove time entries first.']);
            }

            $projectName = $project->name;
            $project->delete();

            DB::commit();

            return redirect()->route('projects.index')
                           ->with('success', "Project '{$projectName}' deleted successfully!");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error deleting project: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to delete project. Please try again.']);
        }
    }

    /**
     * Show the milestones manager for a specific project
     */
    public function milestones(Project $project)
    {
        // Load project with all necessary relationships
        $project->load([
            'customer',
            'billingCompany', 
            'createdByCompany',
            'user',
            'milestones' => function($query) {
                $query->with([
                    'tasks' => function($taskQuery) {
                        $taskQuery->with(['subtasks', 'assignedUser'])
                                 ->orderBy('order');
                    }
                ])->orderBy('order')->orderBy('created_at');
            }
        ]);
        
        // Get milestones for the project
        $milestones = $project->milestones;
        
        // Update project budget calculations
        $project->recalculateBudget();
        
        return view('projects.milestones', compact('project', 'milestones'));
    }

    /**
     * Update project budget settings
     */
    public function updateBudgetSettings(Request $request, Project $project)
    {
        $request->validate([
            'budget_tolerance_percentage' => 'required|numeric|min:0|max:100',
            'budget_warning_percentage' => 'required|numeric|min:0|max:100',
        ]);

        try {
            $project->update([
                'budget_tolerance_percentage' => $request->budget_tolerance_percentage,
                'budget_warning_percentage' => $request->budget_warning_percentage,
            ]);

            // Recalculate budget status with new tolerances
            $project->updateBudgetStatus();

            return back()->with('success', 'Budget settings updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating budget settings: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update budget settings. Please try again.']);
        }
    }

    /**
     * Get project budget overview for API calls
     */
    public function budgetOverview(Project $project)
    {
        $project->load(['milestones.tasks']);
        
        // Update calculations
        $project->recalculateBudget();
        
        return response()->json([
            'project' => [
                'id' => $project->id,
                'name' => $project->name,
                'currency' => $project->currency,
                'budget' => $project->budget,
                'spent' => $project->spent,
                'allocated_budget' => $project->allocated_budget,
                'remaining_budget' => $project->remaining_budget,
                'budget_status' => $project->budget_status,
                'budget_tolerance_percentage' => $project->budget_tolerance_percentage,
                'budget_warning_percentage' => $project->budget_warning_percentage,
            ],
            'budget_summary' => [
                'total_budget' => $project->budget,
                'allocated_budget' => $project->allocated_budget,
                'spent_budget' => $project->spent,
                'remaining_budget' => $project->remaining_budget,
                'budget_status' => $project->budget_status,
                'budget_variance' => $project->getBudgetVariance(),
                'budget_efficiency' => $project->getBudgetEfficiency(),
                'is_over_budget' => $project->isOverBudget(),
                'is_in_warning_zone' => $project->isInWarningZone(),
            ],
            'milestones' => $project->milestones->map(function ($milestone) {
                return [
                    'id' => $milestone->id,
                    'title' => $milestone->title,
                    'budget' => $milestone->price ?? 0,
                    'spent' => $milestone->spent ?? 0,
                    'completion' => $milestone->completion_percentage ?? 0,
                    'status' => $milestone->budget_status ?? 'on_track',
                    'fee_type' => $milestone->fee_type ?? 'in_fee',
                    'pricing_type' => $milestone->pricing_type ?? 'fixed_price',
                    'tasks_count' => $milestone->tasks->count(),
                    'tasks_budget' => $milestone->tasks->sum('price') ?? 0,
                ];
            }),
        ]);
    }

    /**
     * Bulk update project statuses
     */
    public function bulkUpdateStatus(Request $request)
    {
        $request->validate([
            'project_ids' => 'required|array',
            'project_ids.*' => 'exists:projects,id',
            'status' => 'required|in:planning,active,on_hold,completed,cancelled',
        ]);

        try {
            DB::beginTransaction();

            $updatedCount = Project::whereIn('id', $request->project_ids)
                                  ->update(['status' => $request->status]);

            DB::commit();

            return back()->with('success', "Updated status for {$updatedCount} projects.");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error bulk updating project status: ' . $e->getMessage());
            return back()->withErrors(['error' => 'Failed to update project statuses.']);
        }
    }

    /**
     * Export projects to CSV
     */
    public function export(Request $request)
    {
        $query = Project::with(['customer', 'billingCompany', 'user']);

        // Apply same filters as index
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('customer_id')) {
            $query->where('customer_id', $request->customer_id);
        }

        if ($request->filled('company_id')) {
            $query->forCompany($request->company_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('budget_status')) {
            $query->where('budget_status', $request->budget_status);
        }

        $projects = $query->orderBy('created_at', 'desc')->get();

        $filename = 'projects_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($projects) {
            $file = fopen('php://output', 'w');
            
            // CSV Headers
            fputcsv($file, [
                'ID', 'Name', 'Description', 'Status', 'Customer', 'Billing Company',
                'Budget', 'Project Value', 'Spent', 'Remaining', 'Budget Status',
                'Currency', 'Created At', 'Updated At'
            ]);

            // CSV Data
            foreach ($projects as $project) {
                fputcsv($file, [
                    $project->id,
                    $project->name,
                    $project->description,
                    $project->status,
                    $project->customer->name ?? '',
                    $project->billingCompany->name ?? '',
                    $project->budget,
                    $project->project_value,
                    $project->spent,
                    $project->remaining_budget,
                    $project->budget_status,
                    $project->currency,
                    $project->created_at->format('Y-m-d H:i:s'),
                    $project->updated_at->format('Y-m-d H:i:s'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Get project statistics
     */
    public function statistics()
    {
        $stats = [
            'total_projects' => Project::count(),
            'active_projects' => Project::where('status', 'active')->count(),
            'completed_projects' => Project::where('status', 'completed')->count(),
            'over_budget_projects' => Project::where('budget_status', 'over')->count(),
            'warning_budget_projects' => Project::where('budget_status', 'warning')->count(),
            'total_budget' => Project::sum('budget'),
            'total_spent' => Project::sum('spent'),
            'average_budget' => Project::avg('budget'),
            'projects_by_status' => Project::select('status', DB::raw('count(*) as count'))
                                          ->groupBy('status')
                                          ->pluck('count', 'status'),
            'projects_by_budget_status' => Project::select('budget_status', DB::raw('count(*) as count'))
                                                 ->groupBy('budget_status')
                                                 ->pluck('count', 'budget_status'),
        ];

        return response()->json($stats);
    }
}