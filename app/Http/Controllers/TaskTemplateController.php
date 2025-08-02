<?php

namespace App\Http\Controllers;

use App\Models\TaskTemplate;
use App\Models\MilestoneTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TaskTemplateController extends Controller
{
    /**
     * Store a new task template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'milestone_template_id' => 'required|exists:milestone_templates,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_start_date' => 'nullable|date',
            'default_end_date' => 'nullable|date|after_or_equal:default_start_date',
            'fee_type' => 'required|in:in_fee,extended',
            'pricing_type' => 'required|in:fixed_price,hourly_rate',
            'price' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|numeric|min:0',
            'deliverables' => 'nullable|string',
            'checklist_items' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $milestoneTemplate = MilestoneTemplate::findOrFail($validated['milestone_template_id']);
            
            // Get max order
            $maxOrder = $milestoneTemplate->taskTemplates()->max('order') ?? -1;
            
            // Process deliverables
            if (isset($validated['deliverables'])) {
                $validated['deliverables'] = array_filter(array_map('trim', explode("\n", $validated['deliverables'])));
            }
            
            // Process checklist items
            if (isset($validated['checklist_items'])) {
                $validated['checklist_items'] = array_filter(array_map('trim', explode("\n", $validated['checklist_items'])));
            }
            
            // Add order
            $validated['order'] = $maxOrder + 1;
            
            $taskTemplate = TaskTemplate::create($validated);
            
            DB::commit();
            
            // Redirect terug naar milestones view
            return redirect()->route('service-templates.milestones', $milestoneTemplate->serviceTemplate)
                ->with('success', 'Taak toegevoegd');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update a task template
     */
    public function update(Request $request, TaskTemplate $taskTemplate)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_start_date' => 'nullable|date',
            'default_end_date' => 'nullable|date|after_or_equal:default_start_date',
            'fee_type' => 'required|in:in_fee,extended',
            'pricing_type' => 'required|in:fixed_price,hourly_rate',
            'price' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|numeric|min:0',
            'deliverables' => 'nullable|string',
            'checklist_items' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Process deliverables
            if (isset($validated['deliverables'])) {
                $validated['deliverables'] = array_filter(array_map('trim', explode("\n", $validated['deliverables'])));
            }
            
            // Process checklist items
            if (isset($validated['checklist_items'])) {
                $validated['checklist_items'] = array_filter(array_map('trim', explode("\n", $validated['checklist_items'])));
            }
            
            $taskTemplate->update($validated);
            
            DB::commit();
            
            // Redirect terug naar milestones view
            return redirect()->route('service-templates.milestones', $taskTemplate->milestoneTemplate->serviceTemplate)
                ->with('success', 'Taak bijgewerkt');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete a task template
     */
    public function destroy(TaskTemplate $taskTemplate)
    {
        $serviceTemplate = $taskTemplate->milestoneTemplate->serviceTemplate;
        
        try {
            $taskTemplate->delete();
            
            return redirect()->route('service-templates.milestones', $serviceTemplate)
                ->with('success', 'Taak verwijderd');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage());
        }
    }

    /**
     * Redirect subtasks to milestones view
     */
    public function subtasks(TaskTemplate $taskTemplate)
    {
        // Redirect naar de milestones view
        return redirect()->route('service-templates.milestones', [
            'serviceTemplate' => $taskTemplate->milestoneTemplate->serviceTemplate,
            '#task-' . $taskTemplate->id
        ]);
    }

    /**
     * Reorder task templates
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'task_ids' => 'required|array',
            'task_ids.*' => 'exists:task_templates,id'
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['task_ids'] as $index => $taskId) {
                TaskTemplate::where('id', $taskId)->update(['order' => $index]);
            }
            
            DB::commit();
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}