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
                                <a href="{{ route('service-templates.milestones', $taskTemplate->milestoneTemplate->serviceTemplate) }}" class="ml-1 text-gray-700 hover:text-gray-900">
                                    {{ $taskTemplate->milestoneTemplate->serviceTemplate->name }}
                                </a>
                            </div>
                        </li>
                        <li>
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <a href="{{ route('milestone-templates.tasks', $taskTemplate->milestoneTemplate) }}" class="ml-1 text-gray-700 hover:text-gray-900">
                                    {{ $taskTemplate->milestoneTemplate->title }}
                                </a>
                            </div>
                        </li>
                        <li aria-current="page">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="ml-1 text-gray-500">{{ $taskTemplate->title }}</span>
                            </div>
                        </li>
                    </ol>
                </nav>
                <h2 class="mt-2 font-semibold text-xl text-gray-800 leading-tight">
                    Subtaken beheren
                </h2>
            </div>
            <a href="{{ route('milestone-templates.tasks', $taskTemplate->milestoneTemplate) }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                <i class="fas fa-arrow-left mr-1"></i> Terug
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Task Summary -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Taak</h3>
                            <p class="mt-1 text-lg font-semibold">{{ $taskTemplate->title }}</p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Prijs</h3>
                            <p class="mt-1 text-lg font-semibold text-blue-600">
                                €{{ number_format($taskTemplate->calculatePrice(), 2, ',', '.') }}
                            </p>
                        </div>
                        <div>
                            <h3 class="text-sm font-medium text-gray-500">Totaal subtaken</h3>
                            <p class="mt-1 text-lg font-semibold">{{ $taskTemplate->subtaskTemplates->count() }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Subtasks -->
            <div class="space-y-4">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-medium text-gray-900">Subtaken</h3>
                    <button type="button" id="addSubtaskBtn"
                            class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                        <i class="fas fa-plus mr-1"></i> Subtaak toevoegen
                    </button>
                </div>

                <div id="subtasks-container" class="space-y-3">
                    @forelse($taskTemplate->subtaskTemplates as $subtask)
                        <div class="subtask-item bg-white rounded-lg shadow-sm border border-gray-200 p-4" 
                             data-id="{{ $subtask->id }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center flex-1">
                                    <div class="drag-handle cursor-move mr-3 text-gray-400 hover:text-gray-600">
                                        <i class="fas fa-grip-vertical"></i>
                                    </div>
                                    <div class="flex-1">
                                        <h4 class="font-medium text-gray-900">{{ $subtask->title }}</h4>
                                        @if($subtask->description)
                                            <p class="text-sm text-gray-600 mt-1">{{ $subtask->description }}</p>
                                        @endif
                                        @if($subtask->estimated_minutes)
                                            <p class="text-xs text-gray-500 mt-1">
                                                <i class="fas fa-clock mr-1"></i>
                                                Geschatte tijd: {{ $subtask->estimated_minutes }} minuten
                                            </p>
                                        @endif
                                    </div>
                                </div>
                                
                                <div class="flex space-x-1 ml-4">
                                    <button type="button" 
                                            class="edit-subtask-btn text-gray-600 hover:text-gray-800 p-1" 
                                            data-subtask="{{ json_encode($subtask) }}"
                                            title="Bewerken">
                                        <i class="fas fa-edit"></i>
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
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
                            <i class="fas fa-list-check text-gray-400 text-4xl mb-3"></i>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">Nog geen subtaken</h3>
                            <p class="text-gray-500 mb-4">Begin met het toevoegen van subtaken aan deze taak.</p>
                            <button type="button" id="firstSubtaskBtn"
                                    class="bg-green-600 hover:bg-green-700 text-white font-bold py-2 px-4 rounded text-sm">
                                <i class="fas fa-plus mr-1"></i> Eerste subtaak toevoegen
                            </button>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Subtask Modal -->
    <div id="subtaskModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-md bg-white">
            <form id="subtaskForm" method="POST" action="{{ route('subtask-templates.store') }}">
                @csrf
                <input type="hidden" name="task_template_id" value="{{ $taskTemplate->id }}">
                <input type="hidden" id="subtask_method" name="_method" value="POST">
                
                <h3 class="text-lg font-bold text-gray-900 mb-4" id="subtaskModalTitle">Nieuwe Subtaak</h3>
                
                <div class="grid grid-cols-1 gap-4">
                    <!-- Title -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Titel <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="title" 
                               id="subtask_title"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                               required>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Beschrijving
                        </label>
                        <textarea name="description" 
                                  id="subtask_description"
                                  rows="3"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                    </div>

                    <!-- Estimated Minutes -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Geschatte tijd (minuten)
                        </label>
                        <input type="number" 
                               name="estimated_minutes" 
                               id="subtask_estimated_minutes"
                               min="0"
                               step="5"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                               placeholder="Bijv. 30">
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex justify-end space-x-2 mt-6">
                    <button type="button" 
                            id="cancelSubtaskBtn"
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
        const subtaskModal = document.getElementById('subtaskModal');
        const subtaskForm = document.getElementById('subtaskForm');
        const addSubtaskBtn = document.getElementById('addSubtaskBtn');
        const firstSubtaskBtn = document.getElementById('firstSubtaskBtn');
        const cancelSubtaskBtn = document.getElementById('cancelSubtaskBtn');
        
        // Open subtask modal
        function openSubtaskModal() {
            subtaskForm.reset();
            subtaskForm.action = '{{ route('subtask-templates.store') }}';
            document.getElementById('subtask_method').value = 'POST';
            document.getElementById('subtaskModalTitle').textContent = 'Nieuwe Subtaak';
            subtaskModal.classList.remove('hidden');
        }
        
        // Close subtask modal
        function closeSubtaskModal() {
            subtaskModal.classList.add('hidden');
        }
        
        // Edit subtask
        function editSubtask(subtask) {
            subtaskForm.action = `/subtask-templates/${subtask.id}`;
            document.getElementById('subtask_method').value = 'PUT';
            document.getElementById('subtaskModalTitle').textContent = 'Subtaak bewerken';
            
            // Fill form
            document.getElementById('subtask_title').value = subtask.title || '';
            document.getElementById('subtask_description').value = subtask.description || '';
            document.getElementById('subtask_estimated_minutes').value = subtask.estimated_minutes || '';
            
            subtaskModal.classList.remove('hidden');
        }
        
        // Event Listeners
        if (addSubtaskBtn) {
            addSubtaskBtn.addEventListener('click', openSubtaskModal);
        }
        
        if (firstSubtaskBtn) {
            firstSubtaskBtn.addEventListener('click', openSubtaskModal);
        }
        
        if (cancelSubtaskBtn) {
            cancelSubtaskBtn.addEventListener('click', closeSubtaskModal);
        }
        
        // Edit buttons
        document.querySelectorAll('.edit-subtask-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                const subtask = JSON.parse(this.getAttribute('data-subtask'));
                editSubtask(subtask);
            });
        });
        
        // Close modal on background click
        subtaskModal.addEventListener('click', function(e) {
            if (e.target === this) {
                closeSubtaskModal();
            }
        });
        
        // Initialize drag & drop
        const subtasksContainer = document.getElementById('subtasks-container');
        if (subtasksContainer) {
            new Sortable(subtasksContainer, {
                animation: 150,
                handle: '.drag-handle',
                onEnd: function(evt) {
                    const subtaskIds = Array.from(subtasksContainer.children).map(el => el.dataset.id);
                    
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
        }
    });
    </script>
</x-app-layout>