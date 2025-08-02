<?php

namespace App\Http\Controllers;

use App\Models\SubtaskTemplate;
use App\Models\TaskTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SubtaskTemplateController extends Controller
{
    /**
     * Store a new subtask template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_template_id' => 'required|exists:task_templates,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_start_date' => 'nullable|date',
            'default_end_date' => 'nullable|date|after_or_equal:default_start_date',
            'fee_type' => 'required|in:in_fee,extended',
            'pricing_type' => 'required|in:fixed_price,hourly_rate',
            'price' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $taskTemplate = TaskTemplate::findOrFail($validated['task_template_id']);
            
            // Get max order
            $maxOrder = $taskTemplate->subtaskTemplates()->max('order') ?? -1;
            
            // Add order
            $validated['order'] = $maxOrder + 1;
            
            $subtaskTemplate = SubtaskTemplate::create($validated);
            
            DB::commit();
            
            // Redirect terug naar milestones view
            return redirect()->route('service-templates.milestones', $taskTemplate->milestoneTemplate->serviceTemplate)
                ->with('success', 'Subtaak toegevoegd');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update a subtask template
     */
    public function update(Request $request, SubtaskTemplate $subtaskTemplate)
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
        ]);

        DB::beginTransaction();
        try {
            $subtaskTemplate->update($validated);
            
            DB::commit();
            
            // Redirect terug naar milestones view
            return redirect()->route('service-templates.milestones', $subtaskTemplate->taskTemplate->milestoneTemplate->serviceTemplate)
                ->with('success', 'Subtaak bijgewerkt');
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete a subtask template
     */
    public function destroy(SubtaskTemplate $subtaskTemplate)
    {
        $serviceTemplate = $subtaskTemplate->taskTemplate->milestoneTemplate->serviceTemplate;
        
        try {
            $subtaskTemplate->delete();
            
            return redirect()->route('service-templates.milestones', $serviceTemplate)
                ->with('success', 'Subtaak verwijderd');
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage());
        }
    }

    /**
     * Reorder subtask templates
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'subtask_ids' => 'required|array',
            'subtask_ids.*' => 'exists:subtask_templates,id'
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['subtask_ids'] as $index => $subtaskId) {
                SubtaskTemplate::where('id', $subtaskId)->update(['order' => $index]);
            }
            
            DB::commit();
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
}