<!-- Subtask Modal -->
<div id="subtaskModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <form id="subtaskForm" method="POST" action="{{ route('project-subtasks.store') }}">
            @csrf
            <input type="hidden" name="project_task_id" id="subtask_project_task_id">
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
                                id="subtask_fee_type"
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
                                id="subtask_pricing_type"
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
                           id="subtask_estimated_hours"
                           step="0.25"
                           min="0"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Voor planning doeleinden</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-2 mt-6">
                <button type="button" 
                        onclick="closeSubtaskModal()"
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
    function closeSubtaskModal() {
        document.getElementById('subtaskModal').classList.add('hidden');
    }

    function toggleSubtaskPriceFields() {
        // Voor templates tonen we alleen estimated hours
    }

    function editSubtask(subtask, taskId) {
        const form = document.getElementById('subtaskForm');
        form.action = `/project-subtasks/${subtask.id}`;
        document.getElementById('subtask_method').value = 'PUT';
        document.getElementById('subtaskModalTitle').textContent = 'Subtaak bewerken';
        document.getElementById('subtask_project_task_id').value = taskId;
        
        // Fill form
        document.getElementById('subtask_title').value = subtask.title || '';
        document.getElementById('subtask_description').value = subtask.description || '';
        document.getElementById('subtask_fee_type').value = subtask.fee_type || 'in_fee';
        document.getElementById('subtask_pricing_type').value = subtask.pricing_type || 'fixed_price';
        document.getElementById('subtask_estimated_hours').value = subtask.estimated_hours || '';
        
        document.getElementById('subtaskModal').classList.remove('hidden');
    }

    // Close modal on background click
    document.getElementById('subtaskModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeSubtaskModal();
        }
    });
</script>