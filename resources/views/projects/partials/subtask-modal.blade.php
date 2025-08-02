<!-- Subtask Modal -->
<div id="subtaskModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <form id="subtaskForm" method="POST" action="{{ route('subtasks.store') }}">
            @csrf
            <input type="hidden" name="task_id" id="subtask_task_id">
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
                            Startdatum
                        </label>
                        <input type="date" 
                               name="start_date" 
                               id="subtask_start_date"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Einddatum
                        </label>
                        <input type="date" 
                               name="end_date" 
                               id="subtask_end_date"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
                            <option value="extended_fee">Extended (extra kosten)</option>
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

                <!-- Budget Fields -->
                <div class="grid grid-cols-3 gap-4">
                    <div id="subtask_budget_field">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Budget
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">€</span>
                            <input type="number" 
                                   name="budget" 
                                   id="subtask_budget"
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

                <!-- Actual Cost Fields (alleen bij edit) -->
                <div class="grid grid-cols-2 gap-4" id="subtask_actual_fields" style="display: none;">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Uitgegeven bedrag
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">€</span>
                            <input type="number" 
                                   name="spent_amount" 
                                   id="subtask_spent_amount"
                                   step="0.01"
                                   min="0"
                                   class="pl-8 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Werkelijke uren
                        </label>
                        <input type="number" 
                               name="actual_hours" 
                               id="subtask_actual_hours"
                               step="0.25"
                               min="0"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Priority & Status -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Prioriteit
                        </label>
                        <select name="priority" 
                                id="subtask_priority"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="laag">Laag</option>
                            <option value="normaal" selected>Normaal</option>
                            <option value="hoog">Hoog</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Status
                        </label>
                        <select name="status" 
                                id="subtask_status"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value="concept">Concept</option>
                            <option value="in_progress">In uitvoering</option>
                            <option value="completed">Voltooid</option>
                        </select>
                    </div>
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
        const pricingType = document.getElementById('subtask_pricing_type').value;
        const budgetField = document.getElementById('subtask_budget_field');
        const hourlyRateField = document.getElementById('subtask_hourly_rate_field');
        const hoursField = document.getElementById('subtask_hours_field');
        
        if (pricingType === 'fixed_price') {
            budgetField.style.display = 'block';
            hourlyRateField.style.display = 'none';
            hoursField.style.display = 'none';
        } else {
            budgetField.style.display = 'none';
            hourlyRateField.style.display = 'block';
            hoursField.style.display = 'block';
            
            calculateSubtaskBudget();
        }
    }

    function calculateSubtaskBudget() {
        const rate = parseFloat(document.getElementById('subtask_hourly_rate').value) || 0;
        const hours = parseFloat(document.getElementById('subtask_estimated_hours').value) || 0;
        const budget = rate * hours;
        document.getElementById('subtask_budget').value = budget.toFixed(2);
    }

    function editSubtask(subtask, taskId) {
        const form = document.getElementById('subtaskForm');
        form.action = `/subtasks/${subtask.id}`;
        document.getElementById('subtask_method').value = 'PUT';
        document.getElementById('subtaskModalTitle').textContent = 'Subtaak bewerken';
        document.getElementById('subtask_task_id').value = taskId;
        
        // Fill form
        document.getElementById('subtask_title').value = subtask.title || '';
        document.getElementById('subtask_description').value = subtask.description || '';
        
        // Format dates
        if (subtask.start_date) {
            document.getElementById('subtask_start_date').value = subtask.start_date.split(' ')[0];
        }
        if (subtask.end_date) {
            document.getElementById('subtask_end_date').value = subtask.end_date.split(' ')[0];
        }
        
        document.getElementById('subtask_fee_type').value = subtask.fee_type || 'in_fee';
        document.getElementById('subtask_pricing_type').value = subtask.pricing_type || 'fixed_price';
        document.getElementById('subtask_budget').value = subtask.budget || '';
        document.getElementById('subtask_hourly_rate').value = subtask.hourly_rate || 75;
        document.getElementById('subtask_estimated_hours').value = subtask.estimated_hours || '';
        document.getElementById('subtask_priority').value = subtask.priority || 'normaal';
        document.getElementById('subtask_status').value = subtask.status || 'concept';
        
        // Show actual fields for editing
        document.getElementById('subtask_actual_fields').style.display = 'grid';
        document.getElementById('subtask_spent_amount').value = subtask.spent_amount || '';
        document.getElementById('subtask_actual_hours').value = subtask.actual_hours || '';
        
        document.getElementById('subtaskModal').classList.remove('hidden');
        toggleSubtaskPriceFields();
    }

    // Event listeners
    document.getElementById('subtask_pricing_type').addEventListener('change', toggleSubtaskPriceFields);
    document.getElementById('subtask_hourly_rate').addEventListener('input', calculateSubtaskBudget);
    document.getElementById('subtask_estimated_hours').addEventListener('input', calculateSubtaskBudget);
    
    // Close modal on background click
    document.getElementById('subtaskModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeSubtaskModal();
        }
    });
</script>