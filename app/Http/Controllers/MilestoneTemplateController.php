<?php

namespace App\Http\Controllers;

use App\Models\MilestoneTemplate;
use App\Models\ServiceTemplate;
use App\Models\ProjectTemplate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log; // <-- DEZE IMPORT ONTBREEKT!


class MilestoneTemplateController extends Controller
{
    /**
     * Store a new milestone template
     */
    public function store(Request $request)
    {
        // Log voor debugging (kan later verwijderd worden)
        Log::info('Milestone Template Store Request:', $request->all());
        
        $validated = $request->validate([
            'service_template_id' => 'nullable|exists:service_templates,id',
            'project_template_id' => 'nullable|exists:project_templates,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_start_date' => 'nullable|date',
            'default_end_date' => 'nullable|date|after_or_equal:default_start_date',
            'days_from_start' => 'nullable|integer|min:0',
            'duration_days' => 'nullable|integer|min:1',
            'fee_type' => 'required|in:in_fee,extended',
            'pricing_type' => 'required|in:fixed_price,hourly_rate',
            'price' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|numeric|min:0',
            'deliverables' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Bepaal of dit voor service template of project template is
            if (!empty($validated['project_template_id'])) {
                // Voor project templates
                $projectTemplate = ProjectTemplate::findOrFail($validated['project_template_id']);
                
                // Get max order
                $maxOrder = $projectTemplate->milestoneTemplates()->max('order') ?? -1;
                
                // Voor project templates hebben we days_from_start en duration_days nodig
                $validated['days_from_start'] = $validated['days_from_start'] ?? 0;
                $validated['duration_days'] = $validated['duration_days'] ?? 1;
                
                // Geen prijzen voor project templates
                $validated['price'] = 0;
                $validated['hourly_rate'] = 0;
                
                // Update total days van project template
                $totalDays = $validated['days_from_start'] + $validated['duration_days'];
                if ($totalDays > $projectTemplate->total_days) {
                    $projectTemplate->update(['total_days' => $totalDays]);
                }
                
            } else {
                // Voor service templates
                $serviceTemplate = ServiceTemplate::findOrFail($validated['service_template_id']);
                
                // Get max order
                $maxOrder = $serviceTemplate->milestoneTemplates()->max('order') ?? -1;
                
                // Process pricing for service templates
                if ($validated['pricing_type'] === 'hourly_rate') {
                    $validated['price'] = ($validated['hourly_rate'] ?? 0) * ($validated['estimated_hours'] ?? 0);
                }
            }
            
            // Process deliverables
            if (isset($validated['deliverables'])) {
                $validated['deliverables'] = array_filter(array_map('trim', explode("\n", $validated['deliverables'])));
            }
            
            // Add order
            $validated['order'] = $maxOrder + 1;
            
            // Log wat we gaan opslaan
            Log::info('Creating milestone with data:', $validated);
            
            $milestoneTemplate = MilestoneTemplate::create($validated);
            
            DB::commit();
            
            // Redirect based on type
            if (!empty($validated['project_template_id'])) {
                return redirect()->route('project-templates.milestones', $projectTemplate)
                    ->with('success', 'Milestone toegevoegd aan project sjabloon');
            } else {
                return redirect()->route('service-templates.milestones', $serviceTemplate)
                    ->with('success', 'Milestone toegevoegd aan service template');
            }
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error creating milestone template: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Update a milestone template
     */
    public function update(Request $request, MilestoneTemplate $milestoneTemplate)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_start_date' => 'nullable|date',
            'default_end_date' => 'nullable|date|after_or_equal:default_start_date',
            'days_from_start' => 'nullable|integer|min:0',
            'duration_days' => 'nullable|integer|min:1',
            'fee_type' => 'required|in:in_fee,extended',
            'pricing_type' => 'required|in:fixed_price,hourly_rate',
            'price' => 'nullable|numeric|min:0',
            'hourly_rate' => 'nullable|numeric|min:0',
            'estimated_hours' => 'nullable|numeric|min:0',
            'deliverables' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            // Check if this is for project template
            if ($milestoneTemplate->project_template_id) {
                // Voor project templates update days_from_start en duration_days
                $validated['days_from_start'] = $validated['days_from_start'] ?? $milestoneTemplate->days_from_start;
                $validated['duration_days'] = $validated['duration_days'] ?? $milestoneTemplate->duration_days;
                
                // Geen prijzen voor project templates
                $validated['price'] = 0;
                $validated['hourly_rate'] = 0;
                
                // Update total days van project template indien nodig
                $totalDays = $validated['days_from_start'] + $validated['duration_days'];
                $projectTemplate = $milestoneTemplate->projectTemplate;
                if ($totalDays > $projectTemplate->total_days) {
                    $projectTemplate->update(['total_days' => $totalDays]);
                }
            } else {
                // Voor service templates process pricing
                if ($validated['pricing_type'] === 'hourly_rate') {
                    $validated['price'] = ($validated['hourly_rate'] ?? 0) * ($validated['estimated_hours'] ?? 0);
                }
            }
            
            // Process deliverables
            if (isset($validated['deliverables'])) {
                $validated['deliverables'] = array_filter(array_map('trim', explode("\n", $validated['deliverables'])));
            }
            
            $milestoneTemplate->update($validated);
            
            DB::commit();
            
            // Redirect based on type
            if ($milestoneTemplate->project_template_id) {
                return redirect()->route('project-templates.milestones', $milestoneTemplate->projectTemplate)
                    ->with('success', 'Milestone bijgewerkt');
            } else {
                return redirect()->route('service-templates.milestones', $milestoneTemplate->serviceTemplate)
                    ->with('success', 'Milestone bijgewerkt');
            }
                
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Delete a milestone template
     */
    public function destroy(MilestoneTemplate $milestoneTemplate)
    {
        $serviceTemplate = $milestoneTemplate->serviceTemplate;
        $projectTemplate = $milestoneTemplate->projectTemplate;
        
        try {
            $milestoneTemplate->delete();
            
            // Redirect based on type
            if ($projectTemplate) {
                return redirect()->route('project-templates.milestones', $projectTemplate)
                    ->with('success', 'Milestone verwijderd');
            } else {
                return redirect()->route('service-templates.milestones', $serviceTemplate)
                    ->with('success', 'Milestone verwijderd');
            }
                
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden: ' . $e->getMessage());
        }
    }

    /**
     * Redirect tasks view to appropriate milestones view
     */
    public function tasks(MilestoneTemplate $milestoneTemplate)
    {
        // Load all relations
        $milestoneTemplate->load(['taskTemplates.subtaskTemplates']);
        
        // Redirect based on type
        if ($milestoneTemplate->project_template_id) {
            return redirect()->route('project-templates.milestones', [
                'projectTemplate' => $milestoneTemplate->projectTemplate
            ])->with('open_milestone', $milestoneTemplate->id);
        } else {
            return redirect()->route('service-templates.milestones', [
                'serviceTemplate' => $milestoneTemplate->serviceTemplate
            ])->with('open_milestone', $milestoneTemplate->id);
        }
    }

    /**
     * Reorder milestone templates
     */
    public function reorder(Request $request)
    {
        $validated = $request->validate([
            'milestone_ids' => 'required|array',
            'milestone_ids.*' => 'exists:milestone_templates,id',
            'recalculate_days' => 'nullable|boolean'
        ]);

        DB::beginTransaction();
        try {
            foreach ($validated['milestone_ids'] as $index => $milestoneId) {
                MilestoneTemplate::where('id', $milestoneId)->update(['order' => $index]);
            }
            
            // Voor project templates kunnen we optioneel de dagen herberekenen
            if (!empty($validated['recalculate_days'])) {
                $firstMilestone = MilestoneTemplate::find($validated['milestone_ids'][0]);
                if ($firstMilestone && $firstMilestone->project_template_id) {
                    $this->recalculateDaysForProjectTemplate($firstMilestone->project_template_id);
                }
            }
            
            DB::commit();
            
            return response()->json(['success' => true]);
            
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Recalculate days for project template milestones
     */
    private function recalculateDaysForProjectTemplate($projectTemplateId)
    {
        $milestones = MilestoneTemplate::where('project_template_id', $projectTemplateId)
            ->orderBy('order')
            ->get();
            
        $currentDay = 0;
        $maxDay = 0;
        
        foreach ($milestones as $milestone) {
            // Update days_from_start based on position
            $milestone->update(['days_from_start' => $currentDay]);
            
            // Calculate next milestone start
            $currentDay += $milestone->duration_days;
            $maxDay = max($maxDay, $currentDay);
        }
        
        // Update project template total days
        ProjectTemplate::where('id', $projectTemplateId)
            ->update(['total_days' => $maxDay]);
    }
}