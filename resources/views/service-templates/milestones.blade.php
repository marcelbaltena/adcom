<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ $serviceTemplate->name }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Service Template beheren</p>
            </div>
            <div class="flex space-x-2">
                <a href="{{ route('service-templates.edit', $serviceTemplate) }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-edit mr-1"></i> Bewerken
                </a>
                <button type="button" id="cloneToProjectBtn"
                        onclick="openCloneModal({{ $serviceTemplate->id }}, '{{ addslashes($serviceTemplate->name) }}', {{ $serviceTemplate->calculateTotalPrice() }})"
                        class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    <i class="fas fa-copy mr-1"></i> Naar Project
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Service Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Type</h3>
                            <p class="mt-1 text-lg font-semibold">
                                {{ \App\Models\ServiceTemplate::getServiceTypeOptions()[$serviceTemplate->service_type] }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Totaalprijs</h3>
                            <p class="mt-1 text-lg font-semibold text-blue-600">
                                €{{ number_format($serviceTemplate->calculateTotalPrice(), 2, ',', '.') }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Geschatte uren</h3>
                            <p class="mt-1 text-lg font-semibold">
                                {{ number_format($serviceTemplate->getTotalEstimatedHours(), 2, ',', '.') }} uur
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Status</h3>
                            <p class="mt-1">
                                @if($serviceTemplate->is_active)
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Actief
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        Inactief
                                    </span>
                                @endif
                                @if($serviceTemplate->is_popular)
                                    <span class="ml-1 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Populair
                                    </span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($serviceTemplate->description)
                        <div class="mt-6 pt-6 border-t">
                            <h3 class="text-sm font-medium text-gray-500 mb-1">Beschrijving</h3>
                            <p class="text-gray-700">{{ $serviceTemplate->description }}</p>
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
                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Periode</span>
                                </th>
                                <th class="px-4 py-3 text-right w-32">
                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Prijs</span>
                                </th>
                                <th class="px-4 py-3 text-center w-28">
                                    <span class="text-xs font-medium text-gray-500 uppercase tracking-wider">Acties</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200" id="milestones-container">
                            @forelse($serviceTemplate->milestoneTemplates as $milestone)
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
                                        @if($milestone->default_start_date && $milestone->default_end_date)
                                            <span class="text-sm text-gray-600">
                                                {{ $milestone->default_start_date->format('d-m-Y') }} - {{ $milestone->default_end_date->format('d-m-Y') }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-400">Geen datums</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-4 text-right w-32">
                                        <div>
                                            <p class="font-semibold text-gray-900">
                                                €{{ number_format($milestone->calculatePrice(), 2, ',', '.') }}
                                            </p>
                                            @if($milestone->pricing_type === 'hourly_rate')
                                                <p class="text-xs text-gray-500">
                                                    {{ $milestone->estimated_hours }}u × €{{ number_format($milestone->hourly_rate, 2, ',', '.') }}
                                                </p>
                                            @else
                                                <p class="text-xs text-gray-500">Vaste prijs</p>
                                            @endif
                                        </div>
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
                                            <form action="{{ route('milestone-templates.destroy', $milestone) }}" 
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
                                                @foreach($milestone->taskTemplates as $task)
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
                                                            @if($task->default_start_date && $task->default_end_date)
                                                                <span class="text-xs text-gray-600">
                                                                    {{ $task->default_start_date->format('d-m-Y') }} - {{ $task->default_end_date->format('d-m-Y') }}
                                                                </span>
                                                            @else
                                                                <span class="text-xs text-gray-400">Geen datums</span>
                                                            @endif
                                                        </td>
                                                        <td class="px-4 py-3 text-right bg-gray-50 w-32">
                                                            <div>
                                                                <p class="font-medium text-gray-800 text-sm">
                                                                    €{{ number_format($task->calculatePrice(), 2, ',', '.') }}
                                                                </p>
                                                                @if($task->pricing_type === 'hourly_rate')
                                                                    <p class="text-xs text-gray-500">
                                                                        {{ $task->estimated_hours }}u × €{{ number_format($task->hourly_rate, 2, ',', '.') }}
                                                                    </p>
                                                                @endif
                                                            </div>
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
                                                                <form action="{{ route('task-templates.destroy', $task) }}" 
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
                                                                    @foreach($task->subtaskTemplates as $subtask)
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
                                                                                @if($subtask->default_start_date && $subtask->default_end_date)
                                                                                    <span class="text-xs text-gray-500">
                                                                                        {{ $subtask->default_start_date->format('d-m') }} - {{ $subtask->default_end_date->format('d-m') }}
                                                                                    </span>
                                                                                @else
                                                                                    <span class="text-xs text-gray-400">-</span>
                                                                                @endif
                                                                            </td>
                                                                            <td class="px-4 py-2 text-right bg-white w-32">
                                                                                <p class="text-sm text-gray-700">
                                                                                    €{{ number_format($subtask->calculatePrice(), 2, ',', '.') }}
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
                                                                                    <form action="{{ route('subtask-templates.destroy', $subtask) }}" 
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
                                        <p class="text-gray-500 mb-4">Begin met het toevoegen van milestones aan deze service template.</p>
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
                    @if($serviceTemplate->milestoneTemplates->count() > 0)
                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                            <div class="flex items-center justify-between">
                                <div class="text-sm">
                                    <span class="text-gray-500">Totaal milestones:</span>
                                    <span class="font-semibold ml-1">{{ $serviceTemplate->milestoneTemplates->count() }}</span>
                                </div>
                                <div class="flex space-x-6 text-sm">
                                    <div>
                                        <span class="text-gray-500">In Fee:</span>
                                        <span class="font-semibold text-blue-600 ml-1">
                                            €{{ number_format($serviceTemplate->milestoneTemplates->where('fee_type', 'in_fee')->sum(function($m) { return $m->calculatePrice(); }), 2, ',', '.') }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Extended:</span>
                                        <span class="font-semibold text-purple-600 ml-1">
                                            €{{ number_format($serviceTemplate->milestoneTemplates->where('fee_type', 'extended')->sum(function($m) { return $m->calculatePrice(); }), 2, ',', '.') }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Totaal:</span>
                                        <span class="font-semibold text-gray-900 ml-1 text-lg">
                                            €{{ number_format($serviceTemplate->calculateTotalPrice(), 2, ',', '.') }}
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
    @include('service-templates.partials.milestone-modal')
    @include('service-templates.partials.task-modal')
    @include('service-templates.partials.subtask-modal')
    @include('service-templates.partials.clone-modal')

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

        // Modal functions voor taken
        window.openTaskModal = function(milestoneId) {
            const form = document.getElementById('taskForm');
            form.reset();
            form.action = '{{ route('task-templates.store') }}';
            document.getElementById('task_method').value = 'POST';
            document.getElementById('task_milestone_template_id').value = milestoneId;
            document.getElementById('taskModalTitle').textContent = 'Nieuwe Taak';
            document.getElementById('taskModal').classList.remove('hidden');
            toggleTaskPriceFields();
        }

        // Modal functions voor subtaken
        window.openSubtaskModal = function(taskId) {
            const form = document.getElementById('subtaskForm');
            form.reset();
            form.action = '{{ route('subtask-templates.store') }}';
            document.getElementById('subtask_method').value = 'POST';
            document.getElementById('subtask_task_template_id').value = taskId;
            document.getElementById('subtaskModalTitle').textContent = 'Nieuwe Subtaak';
            document.getElementById('subtaskModal').classList.remove('hidden');
            toggleSubtaskPriceFields();
        }

        // Milestone modal functions
        const milestoneModal = document.getElementById('milestoneModal');
        const milestoneForm = document.getElementById('milestoneForm');
        const addMilestoneBtn = document.getElementById('addMilestoneBtn');
        const firstMilestoneBtn = document.getElementById('firstMilestoneBtn');
        const cancelMilestoneBtn = document.getElementById('cancelMilestoneBtn');
        const milestonePricingType = document.getElementById('milestone_pricing_type');
        
        // Open milestone modal
        function openMilestoneModal() {
            milestoneForm.reset();
            milestoneForm.action = '{{ route('milestone-templates.store') }}';
            document.getElementById('milestone_method').value = 'POST';
            document.getElementById('milestoneModalTitle').textContent = 'Nieuwe Milestone';
            milestoneModal.classList.remove('hidden');
            toggleMilestonePriceFields();
        }
        
        // Close milestone modal
        function closeMilestoneModal() {
            milestoneModal.classList.add('hidden');
        }
        
        // Toggle price fields
        function toggleMilestonePriceFields() {
            const pricingType = milestonePricingType.value;
            const priceField = document.getElementById('milestone_price_field');
            const hourlyRateField = document.getElementById('milestone_hourly_rate_field');
            const hoursField = document.getElementById('milestone_hours_field');
            
            if (pricingType === 'fixed_price') {
                priceField.style.display = 'block';
                hourlyRateField.style.display = 'none';
                hoursField.style.display = 'none';
            } else {
                priceField.style.display = 'none';
                hourlyRateField.style.display = 'block';
                hoursField.style.display = 'block';
            }
        }
        
        // Edit milestone
        function editMilestone(milestone) {
            milestoneForm.action = `/milestone-templates/${milestone.id}`;
            document.getElementById('milestone_method').value = 'PUT';
            document.getElementById('milestoneModalTitle').textContent = 'Milestone bewerken';
            
            // Fill form
            document.getElementById('milestone_title').value = milestone.title || '';
            document.getElementById('milestone_description').value = milestone.description || '';
            
            // Format dates correctly for input fields
            if (milestone.default_start_date) {
                const startDate = milestone.default_start_date.split(' ')[0];
                document.getElementById('milestone_default_start_date').value = startDate;
            }
            if (milestone.default_end_date) {
                const endDate = milestone.default_end_date.split(' ')[0];
                document.getElementById('milestone_default_end_date').value = endDate;
            }
            
            document.getElementById('milestone_fee_type').value = milestone.fee_type;
            document.getElementById('milestone_pricing_type').value = milestone.pricing_type;
            document.getElementById('milestone_price').value = milestone.price || '';
            document.getElementById('milestone_hourly_rate').value = milestone.hourly_rate || 75;
            document.getElementById('milestone_estimated_hours').value = milestone.estimated_hours || '';
            
            if (milestone.deliverables && milestone.deliverables.length > 0) {
                document.getElementById('milestone_deliverables').value = milestone.deliverables.join('\n');
            }
            
            milestoneModal.classList.remove('hidden');
            toggleMilestonePriceFields();
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
        
        if (milestonePricingType) {
            milestonePricingType.addEventListener('change', toggleMilestonePriceFields);
        }
        
        // Edit buttons
        document.querySelectorAll('.edit-milestone-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const milestone = JSON.parse(this.getAttribute('data-milestone'));
                editMilestone(milestone);
            });
        });

        // Edit task buttons
        document.querySelectorAll('.edit-task-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const task = JSON.parse(this.getAttribute('data-task'));
                const milestoneId = this.getAttribute('data-milestone-id');
                editTask(task, milestoneId);
            });
        });

        // Edit subtask buttons
        document.querySelectorAll('.edit-subtask-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const subtask = JSON.parse(this.getAttribute('data-subtask'));
                const taskId = this.getAttribute('data-task-id');
                editSubtask(subtask, taskId);
            });
        });
        
        // Close modals on background click
        milestoneModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeMilestoneModal();
            }
        });

        // Initialize drag & drop voor MILESTONES
        const milestonesContainer = document.getElementById('milestones-container');
        if (milestonesContainer) {
            new Sortable(milestonesContainer, {
                animation: 150,
                handle: '.milestone-drag-handle',
                filter: function(evt, target) {
                    // Voorkom dat task/subtask rows als milestone worden gesleept
                    return !target.classList.contains('milestone-item');
                },
                draggable: '.milestone-item',
                onEnd: function(evt) {
                    const milestoneIds = Array.from(milestonesContainer.querySelectorAll('.milestone-item')).map(el => el.dataset.id);
                    
                    fetch('{{ route('milestone-templates.reorder') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            milestone_ids: milestoneIds
                        })
                    });
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
                        // Alleen taken binnen dezelfde milestone kunnen verplaatst worden
                        return to.el.dataset.milestoneId === container.dataset.milestoneId;
                    }
                },
                onEnd: function(evt) {
                    const taskIds = Array.from(container.querySelectorAll('.task-item')).map(el => el.dataset.id);
                    
                    fetch('{{ route('task-templates.reorder') }}', {
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
                        // Alleen subtaken binnen dezelfde taak kunnen verplaatst worden
                        return to.el.dataset.taskId === container.dataset.taskId;
                    }
                },
                onEnd: function(evt) {
                    const subtaskIds = Array.from(container.querySelectorAll('.subtask-item')).map(el => el.dataset.id);
                    
                    fetch('{{ route('subtask-templates.reorder') }}', {
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

        // Clone modal function
        window.openCloneModal = function(serviceId, serviceName, defaultPrice) {
            document.getElementById('serviceName').textContent = serviceName;
            document.getElementById('defaultPrice').textContent = defaultPrice.toFixed(2).replace('.', ',');
            document.getElementById('cloneForm').action = `/service-templates/${serviceId}/clone-to-project`;
            document.getElementById('cloneModal').classList.remove('hidden');
        }

        window.closeCloneModal = function() {
            document.getElementById('cloneModal').classList.add('hidden');
            document.getElementById('cloneForm').reset();
        }

        // Check if we need to open a specific milestone
        @if(session('open_milestone'))
            toggleMilestoneTasks({{ session('open_milestone') }});
        @endif
    });
    </script>
</x-app-layout>