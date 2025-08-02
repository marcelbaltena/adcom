<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CompanyController extends Controller
{
    /**
     * Display a listing of companies
     */
    public function index()
    {
        try {
            $companies = Company::with(['users'])
                ->withCount(['users', 'billingProjects', 'createdProjects'])
                ->paginate(12);

            return view('companies.index', compact('companies'));
        } catch (\Exception $e) {
            Log::error('Error loading companies index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading companies');
        }
    }

    /**
     * Show the form for creating a new company
     */
    public function create()
    {
        return view('companies.create');
    }

    /**
     * Store a newly created company
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'website' => 'nullable|url|max:255',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:100',
                'kvk_number' => 'nullable|string|max:20|unique:companies,kvk_number',
                'vat_number' => 'nullable|string|max:50',
                'iban' => 'nullable|string|max:50',
                'currency' => 'required|string|max:3|in:EUR,USD,GBP',
                'timezone' => 'required|string|max:100',
                'default_hourly_rate' => 'nullable|numeric|min:0|max:999.99',
                'is_active' => 'boolean'
            ]);

            $validated['is_active'] = $request->boolean('is_active', true);

            $company = Company::create($validated);

            Log::info('Company created', ['company_id' => $company->id, 'name' => $company->name]);

            return redirect()->route('companies.show', $company)
                ->with('success', 'Company created successfully!');

        } catch (\Exception $e) {
            Log::error('Error creating company: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating company: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified company
     */
    public function show(Company $company)
    {
        try {
            $company->load([
                'users' => function($query) {
                    $query->orderBy('name');
                },
                'billingProjects' => function($query) {
                    $query->with('milestones')->orderBy('created_at', 'desc');
                },
                'createdProjects' => function($query) {
                    $query->with(['milestones', 'billingCompany'])->orderBy('created_at', 'desc');
                }
            ]);

            return view('companies.show', compact('company'));
        } catch (\Exception $e) {
            Log::error('Error loading company: ' . $e->getMessage());
            return redirect()->route('companies.index')
                ->with('error', 'Error loading company details');
        }
    }

    /**
     * Show the form for editing the specified company
     */
    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    /**
     * Update the specified company
     */
    public function update(Request $request, Company $company)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:50',
                'website' => 'nullable|url|max:255',
                'address' => 'nullable|string|max:255',
                'city' => 'nullable|string|max:100',
                'postal_code' => 'nullable|string|max:20',
                'country' => 'nullable|string|max:100',
                'kvk_number' => [
                    'nullable',
                    'string',
                    'max:20',
                    Rule::unique('companies', 'kvk_number')->ignore($company->id)
                ],
                'vat_number' => 'nullable|string|max:50',
                'iban' => 'nullable|string|max:50',
                'currency' => 'required|string|max:3|in:EUR,USD,GBP',
                'timezone' => 'required|string|max:100',
                'default_hourly_rate' => 'nullable|numeric|min:0|max:999.99',
                'is_active' => 'boolean'
            ]);

            $validated['is_active'] = $request->boolean('is_active', true);

            $company->update($validated);

            Log::info('Company updated', ['company_id' => $company->id, 'name' => $company->name]);

            return redirect()->route('companies.show', $company)
                ->with('success', 'Company updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating company: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating company: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified company
     */
    public function destroy(Company $company)
    {
        try {
            // Check if company has active users or projects
            $activeUsers = $company->users()->where('is_active', true)->count();
            $activeProjects = $company->billingProjects()->count() + $company->createdProjects()->count();

            if ($activeUsers > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete company with active users. Please deactivate or reassign users first.');
            }

            if ($activeProjects > 0) {
                return redirect()->back()
                    ->with('error', 'Cannot delete company with active projects. Please reassign projects first.');
            }

            $companyName = $company->name;
            $company->delete();

            Log::info('Company deleted', ['company_name' => $companyName]);

            return redirect()->route('companies.index')
                ->with('success', "Company '{$companyName}' deleted successfully!");

        } catch (\Exception $e) {
            Log::error('Error deleting company: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting company: ' . $e->getMessage());
        }
    }

    /**
     * Toggle company active status
     */
    public function toggleStatus(Company $company)
    {
        try {
            $company->update(['is_active' => !$company->is_active]);
            
            $status = $company->is_active ? 'activated' : 'deactivated';
            
            Log::info('Company status toggled', [
                'company_id' => $company->id,
                'status' => $status
            ]);

            return response()->json([
                'success' => true,
                'message' => "Company {$status} successfully!",
                'is_active' => $company->is_active
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling company status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating company status'
            ], 500);
        }
    }

    /**
     * Get company users
     */
    public function users(Company $company)
    {
        try {
            $users = $company->users()
                ->with(['assignedTo', 'watching'])
                ->orderBy('name')
                ->get();

            return response()->json([
                'success' => true,
                'users' => $users
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading company users: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading users'
            ], 500);
        }
    }

    /**
     * Add user to company
     */
    public function addUser(Request $request, Company $company)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|exists:users,id',
                'role' => 'required|in:admin,manager,user',
                'hourly_rate' => 'nullable|numeric|min:0|max:999.99'
            ]);

            $user = User::findOrFail($validated['user_id']);
            
            // Update user's company assignment
            $user->update([
                'company_id' => $company->id,
                'role' => $validated['role'],
                'hourly_rate' => $validated['hourly_rate'] ?? $company->default_hourly_rate
            ]);

            Log::info('User added to company', [
                'user_id' => $user->id,
                'company_id' => $company->id,
                'role' => $validated['role']
            ]);

            return response()->json([
                'success' => true,
                'message' => 'User added to company successfully!',
                'user' => $user->fresh()
            ]);

        } catch (\Exception $e) {
            Log::error('Error adding user to company: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error adding user to company'
            ], 500);
        }
    }

    /**
     * Get company statistics
     */
    public function stats(Company $company)
    {
        try {
            $stats = [
                'users_count' => $company->users()->count(),
                'active_users_count' => $company->users()->where('is_active', true)->count(),
                'billing_projects_count' => $company->billingProjects()->count(),
                'created_projects_count' => $company->createdProjects()->count(),
                'total_project_value' => $company->billingProjects()->sum('project_value'),
                'average_hourly_rate' => $company->users()->avg('hourly_rate') ?? $company->default_hourly_rate,
                'recent_projects' => $company->billingProjects()
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(['id', 'name', 'status', 'project_value', 'created_at'])
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading company stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading statistics'
            ], 500);
        }
    }
}