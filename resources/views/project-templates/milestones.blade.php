<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $projectTemplate->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Project sjabloon beheren</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('project-templates.index') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-arrow-left mr-1"></i> Terug naar overzicht
                </a>
                <a href="{{ route('project-templates.edit', $projectTemplate) }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-edit mr-1"></i> Bewerken
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Template Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Categorie</h3>
                            <p class="mt-1 text-lg font-semibold">
                                {{ $projectTemplate->category ?? 'Geen categorie' }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Doorlooptijd</h3>
                            <p class="mt-1 text-lg font-semibold text-blue-600">
                                {{ $projectTemplate->total_days }} dagen
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Geschatte uren</h3>
                            <p class="mt-1 text-lg font-semibold">
                                {{ number_format($projectTemplate->getTotalEstimatedHours(), 2, ',', '.') }} uur
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Gebruik</h3>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    {{ $projectTemplate->usage_count }}x gebruikt
                                </span>
                                @if($projectTemplate->is_active)
                                    <span class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Actief
                                    </span>
                                @else
                                    <span class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Inactief
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($projectTemplate->description)
                        <div class="mt-6 pt-6 border-t">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Beschrijving</h3>
                            <p class="text-gray-700">{{ $projectTemplate->description }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Milestones -->
            <div class="space-y-2">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Milestones</h3>
                    <button type="button" id="addMilestoneBtn"
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                        <i class="fas fa-plus mr-1"></i> Milestone toevoegen
                    </button>
                </div>

                <!-- Table structure -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <!-- Header row -->
                    <table class="w-full">
                        <thead>
                            <tr class="bg-gray-50 border-b border-gray-200">
                                <th class="px-6 py-3 text-left w-auto">
                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Milestone / Taak / Subtaak</span>
                                </th>
                                <th class="px-4 py-3 text-center w-24">
                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Type</span>
                                </th>
                                <th class="px-4 py-3 text-center w-28">
                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Prijstype</span>
                                </th>
                                <th class="px-4 py-3 text-center w-48">
                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Planning</span>
                                </th>
                                <th class="px-4 py-3 text-right w-32">
                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Geschatte uren</span>
                                </th>
                                <th class="px-4 py-3 text-center w-28">
                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Acties</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="milestones-container">
                            @forelse($projectTemplate->milestones as $milestone)
                                <!-- Milestone Row -->
                                <tr class="milestone-item hover:bg-gray-50" data-id="{{ $milestone->id }}">
                                    <td class="px-6 py-4 w-auto">
                                        <div class="flex items-center">
                                            <div class="milestone-drag-handle cursor-move text-gray-400 hover:text-gray-600 mr-3">
                                                <i class="fas fa-grip-vertical"></i>
                                            </div>
                                            <button type="button" 
                                                    onclick="toggleMilestoneTasks({{ $milestone->id }})"
                                                    class="mr-2 text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-chevron-right transition-transform" id="milestone-chevron-{{ $milestone->id }}"></i>
                                            </button>
                                            <div class="flex-1">
                                                <h4 class="font-medium text-gray-900">{{ $milestone->title }}</h4>
                                                @if($milestone->description)
                                                    <p class="text-sm text-gray-500 mt-1">{{ $milestone->description }}</p>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 text-center w-24">
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $milestone->fee_type === 'in_fee' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                            {{ $milestone->fee_type === 'in_fee' ? 'In Fee' : 'Extended' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center w-28">
                                        <span class="text-sm text-gray-600">
                                            {{ $milestone->pricing_type === 'fixed_price' ? 'Vast' : 'Uur' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-center w-48">
                                        <span class="text-sm text-gray-600">
                                            Dag {{ $milestone->days_from_start ?? 0 }} 
                                            ({{ $milestone->duration_days ?? 1 }} {{ ($milestone->duration_days ?? 1) == 1 ? 'dag' : 'dagen' }})
                                        </span>
                                    </td>
                                    <td class="px-4 py-4 text-right w-32">
                                        <p class="font-medium text-gray-900">
                                            {{ number_format($milestone->estimated_hours ?? 0, 1, ',', '.') }} uur
                                        </p>
                                    </td>
                                    <td class="px-4 py-4 text-center w-28">
                                        <div class="flex justify-center space-x-1">
                                            <button type="button"
                                                    onclick="openTaskModal({{ $milestone->id }})"
                                                    class="text-green-600 hover:text-green-800 p-1" 
                                                    title="Taak toevoegen">
                                                <i class="fas fa-plus"></i>
                                            </button>
                                            <button type="button" 
                                                    class="edit-milestone-btn text-gray-600 hover:text-gray-800 p-1" 
                                                    data-milestone="{{ json_encode($milestone) }}"
                                                    title="Bewerken">
                                                <i class="fas fa-edit"></i>
                                            </button>
                                            <form action="{{ route('project-milestones.destroy', $milestone) }}" 
                                                  method="POST" 
                                                  class="inline"
                                                  onsubmit="return confirm('Weet je zeker dat je deze milestone wilt verwijderen?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="text-red-600 hover:text-red-800 p-1" 
                                                        title="Verwijderen">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Tasks Container -->
                                <tr class="hidden" id="milestone-tasks-row-{{ $milestone->id }}">
                                    <td colspan="6" class="p-0">
                                        <table class="w-full">
                                            <tbody class="tasks-container" data-milestone-id="{{ $milestone->id }}">
                                                @foreach($milestone->tasks as $task)
                                                    <tr class="task-item border-t border-gray-100" data-id="{{ $task->id }}" data-milestone="{{ $milestone->id }}">
                                                        <td class="px-6 py-3 pl-16 bg-gray-50 w-auto">
                                                            <div class="flex items-center">
                                                                <div class="task-drag-handle cursor-move text-gray-400 hover:text-gray-600 mr-3">
                                                                    <i class="fas fa-grip-vertical text-sm"></i>
                                                                </div>
                                                                <button type="button" 
                                                                        onclick="toggleTaskSubtasks({{ $task->id }})"
                                                                        class="mr-2 text-gray-400 hover:text-gray-600">
                                                                    <i class="fas fa-chevron-right transition-transform text-xs" id="task-chevron-{{ $task->id }}"></i>
                                                                </button>
                                                                <div class="flex-1">
                                                                    <h5 class="font-medium text-gray-800 text-sm">{{ $task->title }}</h5>
                                                                    @if($task->description)
                                                                        <p class="text-xs text-gray-500 mt-1">{{ $task->description }}</p>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td class="px-4 py-3 text-center bg-gray-50 w-24">
                                                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $task->fee_type === 'in_fee' ? 'bg-blue-50 text-blue-700' : 'bg-purple-50 text-purple-700' }}">
                                                                {{ $task->fee_type === 'in_fee' ? 'In Fee' : 'Extended' }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-3 text-center bg-gray-50 w-28">
                                                            <span class="text-xs text-gray-600">
                                                                {{ $task->pricing_type === 'fixed_price' ? 'Vast' : 'Uur' }}
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-3 text-center bg-gray-50 w-48">
                                                            <span class="text-xs text-gray-600">
                                                                Zelfde als milestone
                                                            </span>
                                                        </td>
                                                        <td class="px-4 py-3 text-right bg-gray-50 w-32">
                                                            <p class="font-medium text-gray-800 text-sm">
                                                                {{ number_format($task->estimated_hours ?? 0, 1, ',', '.') }} uur
                                                            </p>
                                                        </td>
                                                        <td class="px-4 py-3 text-center bg-gray-50 w-28">
                                                            <div class="flex justify-center space-x-1">
                                                                <button type="button"
                                                                        onclick="openSubtaskModal({{ $task->id }})"
                                                                        class="text-green-600 hover:text-green-800 p-1" 
                                                                        title="Subtaak toevoegen">
                                                                    <i class="fas fa-plus text-xs"></i>
                                                                </button>
                                                                <button type="button" 
                                                                        class="edit-task-btn text-gray-600 hover:text-gray-800 p-1" 
                                                                        data-task="{{ json_encode($task) }}"
                                                                        data-milestone-id="{{ $milestone->id }}"
                                                                        title="Bewerken">
                                                                    <i class="fas fa-edit text-xs"></i>
                                                                </button>
                                                                <form action="{{ route('project-tasks.destroy', $task) }}" 
                                                                      method="POST" 
                                                                      class="inline"
                                                                      onsubmit="return confirm('Weet je zeker dat je deze taak wilt verwijderen?');">
                                                                    @csrf
                                                                    @method('DELETE')
                                                                    <button type="submit" 
                                                                            class="text-red-600 hover:text-red-800 p-1" 
                                                                            title="Verwijderen">
                                                                        <i class="fas fa-trash text-xs"></i>
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    <!-- Subtasks Container -->
                                                    <tr class="hidden" id="task-subtasks-row-{{ $task->id }}">
                                                        <td colspan="6" class="p-0">
                                                            <table class="w-full">
                                                                <tbody class="subtasks-container" data-task-id="{{ $task->id }}">
                                                                    @foreach($task->subtasks as $subtask)
                                                                        <tr class="subtask-item border-t border-gray-50" data-id="{{ $subtask->id }}" data-task="{{ $task->id }}">
                                                                            <td class="px-6 py-2 pl-24 bg-white w-auto">
                                                                                <div class="flex items-center">
                                                                                    <div class="subtask-drag-handle cursor-move text-gray-400 hover:text-gray-600 mr-3">
                                                                                        <i class="fas fa-grip-vertical text-xs"></i>
                                                                                    </div>
                                                                                    <span class="text-gray-400 mr-2">•</span>
                                                                                    <div class="flex-1">
                                                                                        <h6 class="text-gray-700 text-sm">{{ $subtask->title }}</h6>
                                                                                        @if($subtask->description)
                                                                                            <p class="text-xs text-gray-500 mt-1">{{ $subtask->description }}</p>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td class="px-4 py-2 text-center bg-white w-24">
                                                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs {{ $subtask->fee_type === 'in_fee' ? 'bg-blue-50 text-blue-600' : 'bg-purple-50 text-purple-600' }}">
                                                                                    {{ $subtask->fee_type === 'in_fee' ? 'In' : 'Ext' }}
                                                                                </span>
                                                                            </td>
                                                                            <td class="px-4 py-2 text-center bg-white w-28">
                                                                                <span class="text-xs text-gray-500">
                                                                                    {{ $subtask->pricing_type === 'fixed_price' ? 'Vast' : 'Uur' }}
                                                                                </span>
                                                                            </td>
                                                                            <td class="px-4 py-2 text-center bg-white w-48">
                                                                                <span class="text-xs text-gray-500">
                                                                                    Zelfde als taak
                                                                                </span>
                                                                            </td>
                                                                            <td class="px-4 py-2 text-right bg-white w-32">
                                                                                <p class="text-sm text-gray-700">
                                                                                    {{ number_format($subtask->estimated_hours ?? 0, 1, ',', '.') }} uur
                                                                                </p>
                                                                            </td>
                                                                            <td class="px-4 py-2 text-center bg-white w-28">
                                                                                <div class="flex justify-center space-x-1">
                                                                                    <button type="button" 
                                                                                            class="edit-subtask-btn text-gray-600 hover:text-gray-800 p-1" 
                                                                                            data-subtask="{{ json_encode($subtask) }}"
                                                                                            data-task-id="{{ $task->id }}"
                                                                                            title="Bewerken">
                                                                                        <i class="fas fa-edit text-xs"></i>
                                                                                    </button>
                                                                                    <form action="{{ route('project-subtasks.destroy', $subtask) }}" 
                                                                                          method="POST" 
                                                                                          class="inline"
                                                                                          onsubmit="return confirm('Weet je zeker dat je deze subtaak wilt verwijderen?');">
                                                                                        @csrf
                                                                                        @method('DELETE')
                                                                                        <button type="submit" 
                                                                                                class="text-red-600 hover:text-red-800 p-1" 
                                                                                                title="Verwijderen">
                                                                                            <i class="fas fa-trash text-xs"></i>
                                                                                        </button>
                                                                                    </form>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-8 text-center">
                                        <i class="fas fa-flag-checkered text-gray-400 text-4xl mb-3"></i>
                                        <h3 class="text-lg font-medium text-gray-900 mb-1">Nog geen milestones</h3>
                                        <p class="text-gray-500 mb-4">Begin met het toevoegen van milestones aan dit project sjabloon.</p>
                                        <button type="button" id="firstMilestoneBtn"
                                                class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                            <i class="fas fa-plus mr-1"></i> Eerste milestone toevoegen
                                        </button>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <!-- Summary Section -->
                    @if($projectTemplate->milestones->count() > 0)
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="text-sm">
                                    <span class="text-gray-500">Totaal milestones:</span>
                                    <span class="font-semibold ml-1">{{ $projectTemplate->milestones->count() }}</span>
                                </div>
                                <div class="flex space-x-6 text-sm">
                                    <div>
                                        <span class="text-gray-500">Totale doorlooptijd:</span>
                                        <span class="font-semibold text-blue-600 ml-1">
                                            {{ $projectTemplate->total_days }} dagen
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Geschatte uren:</span>
                                        <span class="font-semibold text-gray-900 ml-1">
                                            {{ number_format($projectTemplate->getTotalEstimatedHours(), 1, ',', '.') }} uur
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    @include('project-templates.partials.milestone-modal')
    @include('project-templates.partials.task-modal')
    @include('project-templates.partials.subtask-modal')

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Toggle functions
        window.toggleMilestoneTasks = function(milestoneId) {
            const row = document.getElementById(`milestone-tasks-row-${milestoneId}`);
            const chevron = document.getElementById(`milestone-chevron-${milestoneId}`);
            
            if (row.classList.contains('hidden')) {
                row.classList.remove('hidden');
                chevron.classList.add('rotate-90');
            } else {
                row.classList.add('hidden');
                chevron.classList.remove('rotate-90');
                
                // Also hide all subtasks when hiding tasks
                row.querySelectorAll('[id^="task-subtasks-row-"]').forEach(subtaskRow => {
                    subtaskRow.classList.add('hidden');
                });
                row.querySelectorAll('[id^="task-chevron-"]').forEach(subChevron => {
                    subChevron.classList.remove('rotate-90');
                });
            }
        }

        window.toggleTaskSubtasks = function(taskId) {
            const row = document.getElementById(`task-subtasks-row-${taskId}`);
            const chevron = document.getElementById(`task-chevron-${taskId}`);
            
            if (row.classList.contains('hidden')) {
                row.classList.remove('hidden');
                chevron.classList.add('rotate-90');
            } else {
                row.classList.add('hidden');
                chevron.classList.remove('rotate-90');
            }
        }

        // Modal functions voor milestones
        const milestoneModal = document.getElementById('milestoneModal');
        const milestoneForm = document.getElementById('milestoneForm');
        const addMilestoneBtn = document.getElementById('addMilestoneBtn');
        const firstMilestoneBtn = document.getElementById('firstMilestoneBtn');
        const cancelMilestoneBtn = document.getElementById('cancelMilestoneBtn');
        
        // Open milestone modal
        function openMilestoneModal() {
            milestoneForm.reset();
            milestoneForm.action = '{{ route('project-milestones.store') }}';
            document.getElementById('milestone_method').value = 'POST';
            document.getElementById('milestone_project_template_id').value = '{{ $projectTemplate->id }}';
            document.getElementById('milestoneModalTitle').textContent = 'Nieuwe Milestone';
            milestoneModal.classList.remove('hidden');
            toggleMilestonePriceFields();
        }
        
        // Close milestone modal
        function closeMilestoneModal() {
            milestoneModal.classList.add('hidden');
        }
        
        // Event Listeners
        if (addMilestoneBtn) {
            addMilestoneBtn.addEventListener('click', openMilestoneModal);
        }
        
        if (firstMilestoneBtn) {
            firstMilestoneBtn.addEventListener('click', openMilestoneModal);
        }
        
        if (cancelMilestoneBtn) {
            cancelMilestoneBtn.addEventListener('click', closeMilestoneModal);
        }

        // Modal functions voor taken
        window.openTaskModal = function(milestoneId) {
            const form = document.getElementById('taskForm');
            form.reset();
            form.action = '{{ route('project-tasks.store') }}';
            document.getElementById('task_method').value = 'POST';
            document.getElementById('task_project_milestone_id').value = milestoneId;
            document.getElementById('taskModalTitle').textContent = 'Nieuwe Taak';
            document.getElementById('taskModal').classList.remove('hidden');
            toggleTaskPriceFields();
        }

        // Modal functions voor subtaken
        window.openSubtaskModal = function(taskId) {
            const form = document.getElementById('subtaskForm');
            form.reset();
            form.action = '{{ route('project-subtasks.store') }}';
            document.getElementById('subtask_method').value = 'POST';
            document.getElementById('subtask_project_task_id').value = taskId;
            document.getElementById('subtaskModalTitle').textContent = 'Nieuwe Subtaak';
            document.getElementById('subtaskModal').classList.remove('hidden');
            toggleSubtaskPriceFields();
        }

        // Edit milestone
        document.querySelectorAll('.edit-milestone-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const milestone = JSON.parse(this.getAttribute('data-milestone'));
                editMilestone(milestone);
            });
        });

        // Edit task
        document.querySelectorAll('.edit-task-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const task = JSON.parse(this.getAttribute('data-task'));
                const milestoneId = this.getAttribute('data-milestone-id');
                editTask(task, milestoneId);
            });
        });

        // Edit subtask
        document.querySelectorAll('.edit-subtask-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const subtask = JSON.parse(this.getAttribute('data-subtask'));
                const taskId = this.getAttribute('data-task-id');
                editSubtask(subtask, taskId);
            });
        });

        // Close modals on background click
        if (milestoneModal) {
            milestoneModal.addEventListener('click', function(e) {
                if (e.target === this) {
                    closeMilestoneModal();
                }
            });
        }

        // Initialize drag & drop voor MILESTONES
        const milestonesContainer = document.getElementById('milestones-container');
        if (milestonesContainer) {
            new Sortable(milestonesContainer, {
                animation: 150,
                handle: '.milestone-drag-handle',
                filter: function(evt, target) {
                    return !target.classList.contains('milestone-item');
                },
                draggable: '.milestone-item',
                onEnd: function(evt) {
                    const milestoneIds = Array.from(milestonesContainer.querySelectorAll('.milestone-item')).map(el => el.dataset.id);
                    
                    // Update order
                    updateMilestoneOrder(milestoneIds);
                }
            });
        }

        // Initialize drag & drop voor TAKEN
        document.querySelectorAll('.tasks-container').forEach(container => {
            new Sortable(container, {
                animation: 150,
                handle: '.task-drag-handle',
                draggable: '.task-item',
                group: {
                    name: 'tasks',
                    put: function(to) {
                        return to.el.dataset.milestoneId === container.dataset.milestoneId;
                    }
                },
                onEnd: function(evt) {
                    const taskIds = Array.from(container.querySelectorAll('.task-item')).map(el => el.dataset.id);
                    
                    fetch('{{ route('project-tasks.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            task_ids: taskIds
                        })
                    });
                }
            });
        });

        // Initialize drag & drop voor SUBTAKEN
        document.querySelectorAll('.subtasks-container').forEach(container => {
            new Sortable(container, {
                animation: 150,
                handle: '.subtask-drag-handle',
                draggable: '.subtask-item',
                group: {
                    name: 'subtasks',
                    put: function(to) {
                        return to.el.dataset.taskId === container.dataset.taskId;
                    }
                },
                onEnd: function(evt) {
                    const subtaskIds = Array.from(container.querySelectorAll('.subtask-item')).map(el => el.dataset.id);
                    
                    fetch('{{ route('project-subtasks.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            subtask_ids: subtaskIds
                        })
                    });
                }
            });
        });

        function updateMilestoneOrder(milestoneIds) {
            fetch('{{ route('project-milestones.reorder') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    milestone_ids: milestoneIds
                })
            }).then(response => {
                if (response.ok) {
                    // Optioneel: reload om de nieuwe dagen te tonen
                    // location.reload();
                }
            });
        }
    });
    </script>
</x-app-layout>