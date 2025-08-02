<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <div>
                <nav class="flex" aria-label="Breadcrumb">
                    <ol class="inline-flex items-center space-x-1 md:space-x-3">
                        <li>
                            <a href="{{ route('service-templates.index') }}" class="text-gray-700 hover:text-gray-900">
                                Service Templates
                            </a>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ route('service-templates.milestones', $milestoneTemplate->serviceTemplate) }}" class="ml-1 text-gray-700 hover:text-gray-900">
                                    {{ $milestoneTemplate->serviceTemplate->name }}
                                </a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-500">{{ $milestoneTemplate->title }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h2 class="mt-2 font-semibold text-xl text-gray-800 leading-tight">
                    Taken beheren
                </h2>
            </div>
            <a href="{{ route('service-templates.milestones', $milestoneTemplate->serviceTemplate) }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-arrow-left mr-1"></i> Terug
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Milestone Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Milestone</h3>
                            <p class="mt-1 text-lg font-semibold">{{ $milestoneTemplate->title }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Fee Type</h3>
                            <p class="mt-1">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $milestoneTemplate->fee_type === 'in_fee' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                    {{ $milestoneTemplate->fee_type === 'in_fee' ? 'In Fee' : 'Extended' }}
                                </span>
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Prijs</h3>
                            <p class="mt-1 text-lg font-semibold text-blue-600">
                                €{{ number_format($milestoneTemplate->calculatePrice(), 2, ',', '.') }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Totaal taken</h3>
                            <p class="mt-1 text-lg font-semibold">{{ $milestoneTemplate->taskTemplates->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tasks -->
            <div class="space-y-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Taken</h3>
                    <button type="button" id="addTaskBtn"
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                        <i class="fas fa-plus mr-1"></i> Taak toevoegen
                    </button>
                </div>

                <div id="tasks-container" class="space-y-4">
                    @forelse($milestoneTemplate->taskTemplates as $task)
                        <div class="task-item bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden" 
                             data-id="{{ $task->id }}">
                            <div class="p-4">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center">
                                            <div class="drag-handle cursor-move mr-3 text-gray-400 hover:text-gray-600">
                                                <i class="fas fa-grip-vertical"></i>
                                            </div>
                                            <h4 class="text-lg font-medium text-gray-900">
                                                {{ $task->title }}
                                            </h4>
                                            <span class="ml-3 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $task->fee_type === 'in_fee' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                                {{ $task->fee_type === 'in_fee' ? 'In Fee' : 'Extended' }}
                                            </span>
                                        </div>
                                        @if($task->description)
                                            <p class="mt-1 text-sm text-gray-600 ml-9">{{ $task->description }}</p>
                                        @endif
                                    </div>
                                    
                                    <div class="flex items-center space-x-4 ml-4">
                                        <div class="text-right">
                                            <p class="text-lg font-semibold text-gray-900">
                                                €{{ number_format($task->calculatePrice(), 2, ',', '.') }}
                                            </p>
                                            <p class="text-xs text-gray-500">
                                                @if($task->pricing_type === 'hourly_rate')
                                                    {{ $task->estimated_hours }}u × €{{ number_format($task->hourly_rate, 2, ',', '.') }}
                                                @else
                                                    Vaste prijs
                                                @endif
                                            </p>
                                        </div>
                                        
                                        <div class="flex space-x-1">
                                            <a href="{{ route('task-templates.subtasks', $task) }}" 
                                               class="text-blue-600 hover:text-blue-800 p-1" 
                                               title="Subtaken beheren">
                                                <i class="fas fa-tasks"></i>
                                            </a>
                                            <button type="button" 
                                                    class="edit-task-btn text-gray-600 hover:text-gray-800 p-1" 
                                                    data-task="{{ json_encode($task) }}"
                                                    title="Bewerken">
                                                <i class="fas fa-edit"></i>
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
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Subtasks Preview -->
                                @if($task->subtaskTemplates->count() > 0)
                                    <div class="mt-4 ml-9 border-t pt-3">
                                        <div class="text-sm text-gray-600">
                                            <strong>Subtaken:</strong>
                                            @foreach($task->subtaskTemplates->take(3) as $subtask)
                                                <span class="inline-block bg-gray-100 rounded px-2 py-1 text-xs mr-1 mb-1">
                                                    {{ $subtask->title }}
                                                </span>
                                            @endforeach
                                            @if($task->subtaskTemplates->count() > 3)
                                                <span class="text-gray-500">
                                                    +{{ $task->subtaskTemplates->count() - 3 }} meer
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <!-- Checklist Items Preview -->
                                @if($task->checklist_items && count($task->checklist_items) > 0)
                                    <div class="mt-3 ml-9">
                                        <div class="text-sm text-gray-600">
                                            <strong>Checklist:</strong>
                                            <ul class="mt-1 space-y-1">
                                                @foreach(array_slice($task->checklist_items, 0, 3) as $item)
                                                    <li class="flex items-center">
                                                        <i class="fas fa-check-square text-gray-400 mr-2"></i>
                                                        {{ $item }}
                                                    </li>
                                                @endforeach
                                                @if(count($task->checklist_items) > 3)
                                                    <li class="text-gray-500">
                                                        +{{ count($task->checklist_items) - 3 }} meer items
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                            <i class="fas fa-tasks text-gray-400 text-4xl mb-3"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">Nog geen taken</h3>
                            <p class="text-gray-500 mb-4">Begin met het toevoegen van taken aan deze milestone.</p>
                            <button type="button" id="firstTaskBtn"
                                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                <i class="fas fa-plus mr-1"></i> Eerste taak toevoegen
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Task Modal -->
    <div id="taskModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
            <form id="taskForm" method="POST" action="{{ route('task-templates.store') }}">
                @csrf
                <input type="hidden" name="milestone_template_id" value="{{ $milestoneTemplate->id }}">
                <input type="hidden" id="task_method" name="_method" value="POST">
                
                <h3 class="text-lg font-bold text-gray-900 mb-4" id="taskModalTitle">Nieuwe Taak</h3>
                
                <div class="grid grid-cols-1 gap-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Titel <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="title" 
                               id="task_title"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                               required>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Beschrijving
                        </label>
                        <textarea name="description" 
                                  id="task_description"
                                  rows="2"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <!-- Fee Type & Pricing Type -->
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Fee Type <span class="text-red-500">*</span>
                            </label>
                            <select name="fee_type" 
                                    id="task_fee_type"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                    required>
                                <option value="in_fee">In Fee (binnen budget)</option>
                                <option value="extended">Extended (extra kosten)</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Prijstype <span class="text-red-500">*</span>
                            </label>
                            <select name="pricing_type" 
                                    id="task_pricing_type"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                    required>
                                <option value="fixed_price">Vaste prijs</option>
                                <option value="hourly_rate">Uurtarief</option>
                            </select>
                        </div>
                    </div>

                    <!-- Price Fields -->
                    <div class="grid grid-cols-3 gap-4">
                        <div id="task_price_field">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Prijs
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">€</span>
                                <input type="number" 
                                       name="price" 
                                       id="task_price"
                                       step="0.01"
                                       min="0"
                                       class="pl-8 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div id="task_hourly_rate_field" style="display: none;">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Uurtarief
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">€</span>
                                <input type="number" 
                                       name="hourly_rate" 
                                       id="task_hourly_rate"
                                       step="0.01"
                                       min="0"
                                       value="75"
                                       class="pl-8 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div id="task_hours_field" style="display: none;">
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Geschatte uren
                            </label>
                            <input type="number" 
                                   name="estimated_hours" 
                                   id="task_estimated_hours"
                                   step="0.25"
                                   min="0"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Checklist Items -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Checklist Items
                        </label>
                        <textarea name="checklist_items" 
                                  id="task_checklist_items"
                                  rows="4"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                  placeholder="Een item per regel"></textarea>
                        <p class="mt-1 text-xs text-gray-500">Voer elk checklist item op een nieuwe regel in</p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" 
                            id="cancelTaskBtn"
                            class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md transition">
                        Annuleren
                    </button>
                    <button type="submit" 
                            class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition">
                        Opslaan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const taskModal = document.getElementById('taskModal');
        const taskForm = document.getElementById('taskForm');
        const addTaskBtn = document.getElementById('addTaskBtn');
        const firstTaskBtn = document.getElementById('firstTaskBtn');
        const cancelTaskBtn = document.getElementById('cancelTaskBtn');
        const taskPricingType = document.getElementById('task_pricing_type');
        
        // Open task modal
        function openTaskModal() {
            taskForm.reset();
            taskForm.action = '{{ route('task-templates.store') }}';
            document.getElementById('task_method').value = 'POST';
            document.getElementById('taskModalTitle').textContent = 'Nieuwe Taak';
            taskModal.classList.remove('hidden');
            toggleTaskPriceFields();
        }
        
        // Close task modal
        function closeTaskModal() {
            taskModal.classList.add('hidden');
        }
        
        // Toggle price fields
        function toggleTaskPriceFields() {
            const pricingType = taskPricingType.value;
            const priceField = document.getElementById('task_price_field');
            const hourlyRateField = document.getElementById('task_hourly_rate_field');
            const hoursField = document.getElementById('task_hours_field');
            
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
        
        // Edit task
        function editTask(task) {
            taskForm.action = `/task-templates/${task.id}`;
            document.getElementById('task_method').value = 'PUT';
            document.getElementById('taskModalTitle').textContent = 'Taak bewerken';
            
            // Fill form
            document.getElementById('task_title').value = task.title || '';
            document.getElementById('task_description').value = task.description || '';
            document.getElementById('task_fee_type').value = task.fee_type;
            document.getElementById('task_pricing_type').value = task.pricing_type;
            document.getElementById('task_price').value = task.price || '';
            document.getElementById('task_hourly_rate').value = task.hourly_rate || 75;
            document.getElementById('task_estimated_hours').value = task.estimated_hours || '';
            
            if (task.checklist_items && task.checklist_items.length > 0) {
                document.getElementById('task_checklist_items').value = task.checklist_items.join('\n');
            }
            
            taskModal.classList.remove('hidden');
            toggleTaskPriceFields();
        }
        
        // Event Listeners
        if (addTaskBtn) {
            addTaskBtn.addEventListener('click', openTaskModal);
        }
        
        if (firstTaskBtn) {
            firstTaskBtn.addEventListener('click', openTaskModal);
        }
        
        if (cancelTaskBtn) {
            cancelTaskBtn.addEventListener('click', closeTaskModal);
        }
        
        if (taskPricingType) {
            taskPricingType.addEventListener('change', toggleTaskPriceFields);
        }
        
        // Edit buttons
        document.querySelectorAll('.edit-task-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const task = JSON.parse(this.getAttribute('data-task'));
                editTask(task);
            });
        });
        
        // Close modal on background click
        taskModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeTaskModal();
            }
        });
        
        // Initialize drag & drop
        const tasksContainer = document.getElementById('tasks-container');
        if (tasksContainer) {
            new Sortable(tasksContainer, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: function(evt) {
                    const taskIds = Array.from(tasksContainer.children).map(el => el.dataset.id);
                    
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
        }
    });
    </script>
</x-app-layout>