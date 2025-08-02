<!-- Subtask Modal -->
<div id="subtaskModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <form id="subtaskForm" method="POST" action="{{ route('subtask-templates.store') }}">
            @csrf
            <input type="hidden" name="task_template_id" id="subtask_task_template_id">
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

                <!-- Date Fields -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Standaard startdatum
                        </label>
                        <input type="date" 
                               name="default_start_date" 
                               id="subtask_default_start_date"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Wordt gebruikt als default bij toevoegen aan project</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Standaard einddatum
                        </label>
                        <input type="date" 
                               name="default_end_date" 
                               id="subtask_default_end_date"
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

                <!-- Price Fields -->
                <div class="grid grid-cols-3 gap-4">
                    <div id="subtask_price_field">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Prijs
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">€</span>
                            <input type="number" 
                                   name="price" 
                                   id="subtask_price"
                                   step="0.01"
                                   min="0"
                                   class="pl-8 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div id="subtask_hourly_rate_field" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Uurtarief
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">€</span>
                            <input type="number" 
                                   name="hourly_rate" 
                                   id="subtask_hourly_rate"
                                   step="0.01"
                                   min="0"
                                   value="75"
                                   class="pl-8 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div id="subtask_hours_field" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Geschatte uren
                        </label>
                        <input type="number" 
                               name="estimated_hours" 
                               id="subtask_estimated_hours"
                               step="0.25"
                               min="0"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const subtaskModal = document.getElementById('subtaskModal');
    const subtaskForm = document.getElementById('subtaskForm');
    const cancelSubtaskBtn = document.getElementById('cancelSubtaskBtn');
    const subtaskPricingType = document.getElementById('subtask_pricing_type');
    
    // Toggle price fields
    window.toggleSubtaskPriceFields = function() {
        const pricingType = subtaskPricingType.value;
        const priceField = document.getElementById('subtask_price_field');
        const hourlyRateField = document.getElementById('subtask_hourly_rate_field');
        const hoursField = document.getElementById('subtask_hours_field');
        
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
    
    // Edit subtask
    window.editSubtask = function(subtask, taskId) {
        subtaskForm.action = `/subtask-templates/${subtask.id}`;
        document.getElementById('subtask_method').value = 'PUT';
        document.getElementById('subtaskModalTitle').textContent = 'Subtaak bewerken';
        document.getElementById('subtask_task_template_id').value = taskId;
        
        // Fill form
        document.getElementById('subtask_title').value = subtask.title || '';
        document.getElementById('subtask_description').value = subtask.description || '';
        
        // Format dates correctly for input fields
        if (subtask.default_start_date) {
            const startDate = subtask.default_start_date.split(' ')[0];
            document.getElementById('subtask_default_start_date').value = startDate;
        }
        if (subtask.default_end_date) {
            const endDate = subtask.default_end_date.split(' ')[0];
            document.getElementById('subtask_default_end_date').value = endDate;
        }
        
        document.getElementById('subtask_fee_type').value = subtask.fee_type || 'in_fee';
        document.getElementById('subtask_pricing_type').value = subtask.pricing_type || 'fixed_price';
        document.getElementById('subtask_price').value = subtask.price || '';
        document.getElementById('subtask_hourly_rate').value = subtask.hourly_rate || 75;
        document.getElementById('subtask_estimated_hours').value = subtask.estimated_hours || '';
        
        subtaskModal.classList.remove('hidden');
        toggleSubtaskPriceFields();
    }
    
    // Close subtask modal
    function closeSubtaskModal() {
        subtaskModal.classList.add('hidden');
    }
    
    // Event Listeners
    if (cancelSubtaskBtn) {
        cancelSubtaskBtn.addEventListener('click', closeSubtaskModal);
    }
    
    if (subtaskPricingType) {
        subtaskPricingType.addEventListener('change', toggleSubtaskPriceFields);
    }
    
    // Close modal on background click
    subtaskModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeSubtaskModal();
        }
    });
});
</script>