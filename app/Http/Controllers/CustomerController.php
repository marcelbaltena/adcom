<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers
     */
    public function index(Request $request)
    {
        try {
            $query = Customer::with(['company', 'projects'])
                ->withCount(['projects']);

            // Filter by company if specified
            if ($request->has('company_id')) {
                $query->where('company_id', $request->company_id);
            }

            // Filter by type
            if ($request->has('type')) {
                $query->where('type', $request->type);
            }

            // Filter by status
            if ($request->has('status')) {
                if ($request->status === 'active') {
                    $query->where('is_active', true);
                } elseif ($request->status === 'inactive') {
                    $query->where('is_active', false);
                }
            }

            // Search functionality
            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%")
                      ->orWhere('contact_person', 'like', "%{$search}%")
                      ->orWhere('city', 'like', "%{$search}%");
                });
            }

            $customers = $query->orderBy('name')->paginate(12);
            $companies = Company::active()->orderBy('name')->get();

            return view('customers.index', compact('customers', 'companies'));
        } catch (\Exception $e) {
            Log::error('Error loading customers index: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error loading customers');
        }
    }

    /**
     * Show the form for creating a new customer
     */
    public function create()
    {
        $companies = Company::active()->orderBy('name')->get();
        return view('customers.create', compact('companies'));
    }

    /**
     * Store a newly created customer
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'company_id' => 'required|exists:companies,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'required|in:individual,company',
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
                    Rule::unique('customers')->where('company_id', $request->company_id)
                ],
                'vat_number' => 'nullable|string|max:50',
                'iban' => 'nullable|string|max:50',
                'contact_person' => 'nullable|string|max:255',
                'contact_email' => 'nullable|email|max:255',
                'contact_phone' => 'nullable|string|max:50',
                'currency' => 'required|string|max:3|in:EUR,USD,GBP',
                'default_hourly_rate' => 'nullable|numeric|min:0|max:999.99',
                'payment_terms' => 'required|in:7,14,30,60,90',
                'industry' => 'nullable|string|max:100',
                'size' => 'nullable|in:small,medium,large,enterprise',
                'notes' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            $validated['is_active'] = $request->boolean('is_active', true);

            $customer = Customer::create($validated);

            Log::info('Customer created', [
                'customer_id' => $customer->id,
                'name' => $customer->name,
                'company_id' => $customer->company_id
            ]);

            return redirect()->route('customers.show', $customer)
                ->with('success', 'Customer created successfully!');

        } catch (\Exception $e) {
            Log::error('Error creating customer: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error creating customer: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified customer
     */
    public function show(Customer $customer)
    {
        try {
            $customer->load([
                'company',
                'projects' => function($query) {
                    $query->with(['milestones'])->orderBy('created_at', 'desc');
                }
            ]);

            return view('customers.show', compact('customer'));
        } catch (\Exception $e) {
            Log::error('Error loading customer: ' . $e->getMessage());
            return redirect()->route('customers.index')
                ->with('error', 'Error loading customer details');
        }
    }

    /**
     * Show the form for editing the specified customer
     */
    public function edit(Customer $customer)
    {
        $companies = Company::active()->orderBy('name')->get();
        return view('customers.edit', compact('customer', 'companies'));
    }

    /**
     * Update the specified customer
     */
    public function update(Request $request, Customer $customer)
    {
        try {
            $validated = $request->validate([
                'company_id' => 'required|exists:companies,id',
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'type' => 'required|in:individual,company',
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
                    Rule::unique('customers')->where('company_id', $request->company_id)->ignore($customer->id)
                ],
                'vat_number' => 'nullable|string|max:50',
                'iban' => 'nullable|string|max:50',
                'contact_person' => 'nullable|string|max:255',
                'contact_email' => 'nullable|email|max:255',
                'contact_phone' => 'nullable|string|max:50',
                'currency' => 'required|string|max:3|in:EUR,USD,GBP',
                'default_hourly_rate' => 'nullable|numeric|min:0|max:999.99',
                'payment_terms' => 'required|in:7,14,30,60,90',
                'industry' => 'nullable|string|max:100',
                'size' => 'nullable|in:small,medium,large,enterprise',
                'notes' => 'nullable|string',
                'is_active' => 'boolean'
            ]);

            $validated['is_active'] = $request->boolean('is_active', true);

            $customer->update($validated);

            Log::info('Customer updated', [
                'customer_id' => $customer->id,
                'name' => $customer->name
            ]);

            return redirect()->route('customers.show', $customer)
                ->with('success', 'Customer updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating customer: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Error updating customer: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified customer
     */
    public function destroy(Customer $customer)
    {
        try {
            // Check if customer has projects
            if ($customer->projects()->exists()) {
                return redirect()->back()
                    ->with('error', 'Cannot delete customer with existing projects. Please reassign or delete projects first.');
            }

            $customerName = $customer->name;
            $customer->delete();

            Log::info('Customer deleted', ['customer_name' => $customerName]);

            return redirect()->route('customers.index')
                ->with('success', "Customer '{$customerName}' deleted successfully!");

        } catch (\Exception $e) {
            Log::error('Error deleting customer: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error deleting customer: ' . $e->getMessage());
        }
    }

    /**
     * Toggle customer active status
     */
    public function toggleStatus(Customer $customer)
    {
        try {
            $customer->update(['is_active' => !$customer->is_active]);
            
            $status = $customer->is_active ? 'activated' : 'deactivated';
            
            Log::info('Customer status toggled', [
                'customer_id' => $customer->id,
                'status' => $status
            ]);

            return response()->json([
                'success' => true,
                'message' => "Customer {$status} successfully!",
                'is_active' => $customer->is_active
            ]);

        } catch (\Exception $e) {
            Log::error('Error toggling customer status: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error updating customer status'
            ], 500);
        }
    }

    /**
     * Get customer statistics
     */
    public function stats(Customer $customer)
    {
        try {
            $stats = [
                'projects_count' => $customer->projects()->count(),
                'active_projects_count' => $customer->getActiveProjectsCount(),
                'completed_projects_count' => $customer->getCompletedProjectsCount(),
                'total_project_value' => $customer->getTotalProjectValue(),
                'recent_projects' => $customer->projects()
                    ->orderBy('created_at', 'desc')
                    ->limit(5)
                    ->get(['id', 'name', 'status', 'project_value', 'created_at'])
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error loading customer stats: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error loading statistics'
            ], 500);
        }
    }

    /**
     * Create project for customer
     */
    public function createProject(Customer $customer)
    {
        // Redirect to project creation with customer pre-selected
        return redirect()->route('projects.create', ['customer_id' => $customer->id]);
    }
}