<!-- Task Modal -->
<div id="taskModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <form id="taskForm" method="POST" action="{{ route('task-templates.store') }}">
            @csrf
            <input type="hidden" name="milestone_template_id" id="task_milestone_template_id">
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

                <!-- Date Fields -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Standaard startdatum
                        </label>
                        <input type="date" 
                               name="default_start_date" 
                               id="task_default_start_date"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Wordt gebruikt als default bij toevoegen aan project</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Standaard einddatum
                        </label>
                        <input type="date" 
                               name="default_end_date" 
                               id="task_default_end_date"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Wordt gebruikt als default bij toevoegen aan project</p>
                    </div>
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

                <!-- Deliverables -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Deliverables
                    </label>
                    <textarea name="deliverables" 
                              id="task_deliverables"
                              rows="3"
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Een item per regel"></textarea>
                    <p class="mt-1 text-xs text-gray-500">Voer elk deliverable op een nieuwe regel in</p>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const taskModal = document.getElementById('taskModal');
    const taskForm = document.getElementById('taskForm');
    const cancelTaskBtn = document.getElementById('cancelTaskBtn');
    const taskPricingType = document.getElementById('task_pricing_type');
    
    // Toggle price fields
    window.toggleTaskPriceFields = function() {
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
    window.editTask = function(task, milestoneId) {
        taskForm.action = `/task-templates/${task.id}`;
        document.getElementById('task_method').value = 'PUT';
        document.getElementById('taskModalTitle').textContent = 'Taak bewerken';
        document.getElementById('task_milestone_template_id').value = milestoneId;
        
        // Fill form
        document.getElementById('task_title').value = task.title || '';
        document.getElementById('task_description').value = task.description || '';
        
        // Format dates correctly for input fields
        if (task.default_start_date) {
            const startDate = task.default_start_date.split(' ')[0];
            document.getElementById('task_default_start_date').value = startDate;
        }
        if (task.default_end_date) {
            const endDate = task.default_end_date.split(' ')[0];
            document.getElementById('task_default_end_date').value = endDate;
        }
        
        document.getElementById('task_fee_type').value = task.fee_type;
        document.getElementById('task_pricing_type').value = task.pricing_type;
        document.getElementById('task_price').value = task.price || '';
        document.getElementById('task_hourly_rate').value = task.hourly_rate || 75;
        document.getElementById('task_estimated_hours').value = task.estimated_hours || '';
        
        if (task.deliverables && task.deliverables.length > 0) {
            document.getElementById('task_deliverables').value = task.deliverables.join('\n');
        }
        
        if (task.checklist_items && task.checklist_items.length > 0) {
            document.getElementById('task_checklist_items').value = task.checklist_items.join('\n');
        }
        
        taskModal.classList.remove('hidden');
        toggleTaskPriceFields();
    }
    
    // Close task modal
    function closeTaskModal() {
        taskModal.classList.add('hidden');
    }
    
    // Event Listeners
    if (cancelTaskBtn) {
        cancelTaskBtn.addEventListener('click', closeTaskModal);
    }
    
    if (taskPricingType) {
        taskPricingType.addEventListener('change', toggleTaskPriceFields);
    }
    
    // Close modal on background click
    taskModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeTaskModal();
        }
    });
});
</script>