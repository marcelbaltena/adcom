<?php

namespace App\Http\Controllers;

use App\Models\ProjectTemplate;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProjectTemplateController extends Controller
{
    /**
     * Display a listing of project templates
     */
    public function index()
    {
        $templates = ProjectTemplate::with('milestones')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
            
        return view('project-templates.index', compact('templates'));
    }

    /**
     * Show the form for creating a new project template
     */
    public function create()
    {
        return view('project-templates.create');
    }

    /**
     * Store a newly created project template
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100'
        ]);
        
        $validated['created_by'] = auth()->id();
        $validated['is_active'] = true;
        $validated['usage_count'] = 0;
        $validated['total_days'] = 0;
        
        $template = ProjectTemplate::create($validated);
        
        return redirect()->route('project-templates.milestones', $template)
            ->with('success', 'Project sjabloon aangemaakt. Je kunt nu milestones toevoegen.');
    }

    /**
     * Display milestones for a project template
     */
    public function milestones(ProjectTemplate $projectTemplate)
    {
        // Load met de nieuwe relatie namen
        $projectTemplate->load([
            'milestones.tasks.subtasks'
        ]);
        
        return view('project-templates.milestones', compact('projectTemplate'));
    }

    /**
     * Show the form for editing a project template
     */
    public function edit(ProjectTemplate $projectTemplate)
    {
        return view('project-templates.edit', compact('projectTemplate'));
    }

    /**
     * Update the specified project template
     */
    public function update(Request $request, ProjectTemplate $projectTemplate)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'is_active' => 'boolean'
        ]);
        
        // Zorg dat is_active altijd een boolean is
        $validated['is_active'] = $request->boolean('is_active');
        
        $projectTemplate->update($validated);
        
        return redirect()->route('project-templates.index')
            ->with('success', 'Project sjabloon bijgewerkt');
    }

    /**
     * Remove the specified project template
     */
    public function destroy(ProjectTemplate $projectTemplate)
    {
        if ($projectTemplate->usage_count > 0) {
            return redirect()->back()
                ->with('error', 'Dit sjabloon is al gebruikt en kan niet worden verwijderd.');
        }
        
        // Check of er milestones zijn
        if ($projectTemplate->milestones()->count() > 0) {
            return redirect()->back()
                ->with('error', 'Dit sjabloon heeft milestones en kan niet worden verwijderd. Verwijder eerst alle milestones.');
        }
        
        $projectTemplate->delete();
        
        return redirect()->route('project-templates.index')
            ->with('success', 'Project sjabloon verwijderd');
    }

    /**
     * Show form to apply template to a project
     */
    public function showApplyForm(Project $project)
    {
        $templates = ProjectTemplate::where('is_active', true)
            ->with('milestones')
            ->orderBy('name')
            ->get();
            
        return view('project-templates.apply', compact('project', 'templates'));
    }

    /**
     * Apply a template to a project
     */
    public function apply(Request $request, Project $project)
    {
        $validated = $request->validate([
            'project_template_id' => 'required|exists:project_templates,id',
            'start_date' => 'required|date'
        ]);
        
        try {
            $template = ProjectTemplate::with(['milestones.tasks.subtasks'])
                ->findOrFail($validated['project_template_id']);
                
            $startDate = Carbon::parse($validated['start_date']);
            
            // Check of project al milestones heeft
            if ($project->milestones()->count() > 0) {
                return redirect()->back()
                    ->with('warning', 'Dit project heeft al milestones. Weet je zeker dat je het sjabloon wilt toepassen?')
                    ->withInput();
            }
            
            // Clone template to project
            $template->cloneToProject($project, $startDate);
            
            return redirect()->route('projects.milestones', $project)
                ->with('success', sprintf(
                    'Sjabloon "%s" succesvol toegepast op het project. Er zijn %d milestones toegevoegd.',
                    $template->name,
                    $template->milestones->count()
                ));
                
        } catch (\Exception $e) {
            \Log::error('Error applying template to project: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden bij het toepassen van het sjabloon: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Toggle active status of a template (AJAX)
     */
    public function toggleActive(ProjectTemplate $projectTemplate)
    {
        $projectTemplate->update([
            'is_active' => !$projectTemplate->is_active
        ]);
        
        return response()->json([
            'success' => true,
            'is_active' => $projectTemplate->is_active,
            'message' => $projectTemplate->is_active 
                ? 'Sjabloon is nu actief' 
                : 'Sjabloon is nu inactief'
        ]);
    }

    /**
     * Duplicate a project template
     */
    public function duplicate(ProjectTemplate $projectTemplate)
    {
        DB::beginTransaction();
        
        try {
            // Create new template
            $newTemplate = $projectTemplate->replicate();
            $newTemplate->name = $projectTemplate->name . ' (kopie)';
            $newTemplate->usage_count = 0;
            $newTemplate->created_by = auth()->id();
            $newTemplate->save();
            
            // Clone all milestones, tasks and subtasks
            foreach ($projectTemplate->milestones as $milestone) {
                $newMilestone = $milestone->replicate();
                $newMilestone->project_template_id = $newTemplate->id;
                $newMilestone->save();
                
                foreach ($milestone->tasks as $task) {
                    $newTask = $task->replicate();
                    $newTask->project_milestone_id = $newMilestone->id;
                    $newTask->save();
                    
                    foreach ($task->subtasks as $subtask) {
                        $newSubtask = $subtask->replicate();
                        $newSubtask->project_task_id = $newTask->id;
                        $newSubtask->save();
                    }
                }
            }
            
            DB::commit();
            
            return redirect()->route('project-templates.milestones', $newTemplate)
                ->with('success', 'Sjabloon succesvol gedupliceerd');
                
        } catch (\Exception $e) {
            DB::rollback();
            
            return redirect()->back()
                ->with('error', 'Er is een fout opgetreden bij het dupliceren: ' . $e->getMessage());
        }
    }

    /**
     * Get templates for AJAX requests
     */
    public function getTemplatesJson()
    {
        $templates = ProjectTemplate::where('is_active', true)
            ->with(['milestones' => function($query) {
                $query->orderBy('order');
            }])
            ->orderBy('name')
            ->get()
            ->map(function($template) {
                return [
                    'id' => $template->id,
                    'name' => $template->name,
                    'category' => $template->category,
                    'description' => $template->description,
                    'milestone_count' => $template->milestones->count(),
                    'total_days' => $template->total_days,
                    'estimated_hours' => $template->getTotalEstimatedHours()
                ];
            });
            
        return response()->json($templates);
    }

    /**
     * Preview template details (AJAX)
     */
    public function preview(ProjectTemplate $projectTemplate)
    {
        $projectTemplate->load([
            'milestones.tasks.subtasks'
        ]);
        
        return response()->json([
            'template' => $projectTemplate,
            'statistics' => [
                'total_milestones' => $projectTemplate->milestones->count(),
                'total_tasks' => $projectTemplate->milestones->sum(function($m) {
                    return $m->tasks->count();
                }),
                'total_subtasks' => $projectTemplate->milestones->sum(function($m) {
                    return $m->tasks->sum(function($t) {
                        return $t->subtasks->count();
                    });
                }),
                'total_hours' => $projectTemplate->getTotalEstimatedHours(),
                'total_days' => $projectTemplate->total_days
            ]
        ]);
    }
}