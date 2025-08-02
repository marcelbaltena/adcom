<?php

namespace App\Http\Controllers;

use App\Models\ServiceTemplate;
use App\Models\Project;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ServiceTemplateController extends Controller
{
    /**
     * Display a listing of service templates
     */
    public function index(Request $request)
    {
        $query = ServiceTemplate::with(['milestoneTemplates.taskTemplates.subtaskTemplates']);

        // Filter by category
        if ($request->filled('category')) {
            $query->where('category', $request->category);
        }

        // Filter by active status
        if ($request->filled('active')) {
            $query->where('is_active', $request->active === 'yes');
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $services = $query->orderBy('category')
                         ->orderBy('order')
                         ->orderBy('name')
                         ->paginate(12);

        $categories = ServiceTemplate::getCategories();

        return view('service-templates.index', compact('services', 'categories'));
    }

    /**
     * Show the form for creating a new service template
     */
    public function create()
    {
        $categories = ServiceTemplate::getCategories();
        return view('service-templates.create', compact('categories'));
    }

    /**
     * Store a newly created service template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'service_type' => 'required|in:hourly,fixed,package',
            'base_price' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'tags' => 'nullable|string'
        ]);

        try {
            DB::beginTransaction();

            // Process tags
            if ($request->filled('tags')) {
                $validated['tags'] = array_map('trim', explode(',', $request->tags));
            }

            // company_id is optional
            $validated['company_id'] = auth()->user()->company_id ?? null;
            $validated['is_active'] = $request->boolean('is_active', true);
            $validated['is_popular'] = $request->boolean('is_popular', false);

            $serviceTemplate = ServiceTemplate::create($validated);

            DB::commit();

            return redirect()->route('service-templates.milestones', $serviceTemplate)
                           ->with('success', 'Service template created! Now add milestones.');

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating service template: ' . $e->getMessage());
            return back()->withInput()
                        ->with('error', 'Error creating service template: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified service template
     */
    public function show(ServiceTemplate $serviceTemplate)
    {
        $serviceTemplate->load(['milestoneTemplates.taskTemplates.subtaskTemplates']);
        
        return view('service-templates.show', compact('serviceTemplate'));
    }

    /**
     * Show the form for editing the service template
     */
    public function edit(ServiceTemplate $serviceTemplate)
    {
        $categories = ServiceTemplate::getCategories();
        
        return view('service-templates.edit', compact('serviceTemplate', 'categories'));
    }

    /**
     * Update the specified service template
     */
    public function update(Request $request, ServiceTemplate $serviceTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'service_type' => 'required|in:hourly,fixed,package',
            'base_price' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'is_popular' => 'boolean',
            'tags' => 'nullable|string'
        ]);

        try {
            // Process tags
            if ($request->filled('tags')) {
                $validated['tags'] = array_map('trim', explode(',', $request->tags));
            }

            $validated['is_active'] = $request->boolean('is_active');
            $validated['is_popular'] = $request->boolean('is_popular');

            $serviceTemplate->update($validated);

            return redirect()->route('service-templates.show', $serviceTemplate)
                           ->with('success', 'Service template updated successfully!');

        } catch (\Exception $e) {
            Log::error('Error updating service template: ' . $e->getMessage());
            return back()->withInput()
                        ->with('error', 'Error updating service template.');
        }
    }

    /**
     * Remove the specified service template
     */
    public function destroy(ServiceTemplate $serviceTemplate)
    {
        try {
            $serviceTemplate->delete();
            
            return redirect()->route('service-templates.index')
                           ->with('success', 'Service template deleted successfully!');
                           
        } catch (\Exception $e) {
            Log::error('Error deleting service template: ' . $e->getMessage());
            return back()->with('error', 'Error deleting service template.');
        }
    }

    /**
     * Show milestones management page
     */
    public function milestones(ServiceTemplate $serviceTemplate)
    {
        $serviceTemplate->load(['milestoneTemplates.taskTemplates.subtaskTemplates']);
        
        return view('service-templates.milestones', compact('serviceTemplate'));
    }

    /**
     * Clone service template to project
     */
    public function cloneToProject(Request $request, ServiceTemplate $serviceTemplate)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'custom_price' => 'nullable|numeric|min:0',
            'apply_discount' => 'boolean',
            'discount_percentage' => 'nullable|numeric|min:0|max:100'
        ]);

        $project = Project::findOrFail($request->project_id);

        try {
            DB::beginTransaction();

            // Calculate price with discount if applicable
            $basePrice = $request->custom_price ?? $serviceTemplate->calculateTotalPrice();
            
            if ($request->boolean('apply_discount') && $request->discount_percentage) {
                $discount = ($basePrice * $request->discount_percentage) / 100;
                $finalPrice = $basePrice - $discount;
            } else {
                $finalPrice = $basePrice;
            }

            // Clone with options
            $options = [
                'price' => $finalPrice,
                'original_price' => $basePrice,
                'discount_applied' => $request->discount_percentage ?? 0
            ];

            $milestones = $serviceTemplate->cloneToProject($project, $options);

            DB::commit();

            // Log activity
            Log::info('Service template cloned to project', [
                'service_template_id' => $serviceTemplate->id,
                'project_id' => $project->id,
                'milestones_created' => count($milestones)
            ]);

            return redirect()
                ->route('projects.milestones', $project)
                ->with('success', "Service '{$serviceTemplate->name}' successfully added to project!");

        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error cloning service template: ' . $e->getMessage());
            return back()->with('error', 'Error adding service to project.');
        }
    }

    /**
     * Toggle active status
     */
    public function toggleActive(ServiceTemplate $serviceTemplate)
    {
        try {
            $serviceTemplate->update(['is_active' => !$serviceTemplate->is_active]);
            
            return response()->json([
                'success' => true,
                'is_active' => $serviceTemplate->is_active
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status'
            ], 500);
        }
    }

    /**
     * Get projects for dropdown (AJAX)
     */
    public function getProjects(Request $request)
    {
        $projects = Project::with('customer')
            ->where('status', 'active')
            ->when($request->search, function($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                      ->orWhereHas('customer', function($q) use ($search) {
                          $q->where('name', 'like', "%{$search}%");
                      });
            })
            ->limit(20)
            ->get()
            ->map(function($project) {
                return [
                    'id' => $project->id,
                    'text' => $project->name . ' - ' . $project->customer->name
                ];
            });

        return response()->json(['results' => $projects]);
    }
}