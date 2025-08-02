<!-- Task Modal -->
<div id="taskModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <form id="taskForm" method="POST" action="{{ route('project-tasks.store') }}">
            @csrf
            <input type="hidden" name="project_milestone_id" id="task_project_milestone_id">
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

                <!-- Estimated Hours -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Geschatte uren
                    </label>
                    <input type="number" 
                           name="estimated_hours" 
                           id="task_estimated_hours"
                           step="0.25"
                           min="0"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Voor planning doeleinden, prijzen worden bij project bepaald</p>
                </div>

                <!-- Deliverables -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Deliverables
                    </label>
                    <textarea name="deliverables" 
                              id="task_deliverables"
                              rows="2"
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Een item per regel"></textarea>
                </div>

                <!-- Checklist Items -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Checklist items
                    </label>
                    <textarea name="checklist_items" 
                              id="task_checklist_items"
                              rows="3"
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Een item per regel"></textarea>
                    <p class="mt-1 text-xs text-gray-500">Voer elk checklist item op een nieuwe regel in</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-2 mt-6">
                <button type="button" 
                        onclick="closeTaskModal()"
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

<script>
    function closeTaskModal() {
        document.getElementById('taskModal').classList.add('hidden');
    }

    function toggleTaskPriceFields() {
        // Voor templates tonen we alleen estimated hours
    }

    function editTask(task, milestoneId) {
        const form = document.getElementById('taskForm');
        form.action = `/project-tasks/${task.id}`;
        document.getElementById('task_method').value = 'PUT';
        document.getElementById('taskModalTitle').textContent = 'Taak bewerken';
        document.getElementById('task_project_milestone_id').value = milestoneId;
        
        // Fill form
        document.getElementById('task_title').value = task.title || '';
        document.getElementById('task_description').value = task.description || '';
        document.getElementById('task_fee_type').value = task.fee_type || 'in_fee';
        document.getElementById('task_pricing_type').value = task.pricing_type || 'fixed_price';
        document.getElementById('task_estimated_hours').value = task.estimated_hours || '';
        
        if (task.deliverables && task.deliverables.length > 0) {
            document.getElementById('task_deliverables').value = task.deliverables.join('\n');
        }
        
        if (task.checklist_items && task.checklist_items.length > 0) {
            document.getElementById('task_checklist_items').value = task.checklist_items.join('\n');
        }
        
        document.getElementById('taskModal').classList.remove('hidden');
    }

    // Close modal on background click
    document.getElementById('taskModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeTaskModal();
        }
    });
</script>