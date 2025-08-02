<!-- Milestone Modal -->
<div id="milestoneModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <form id="milestoneForm" method="POST" action="{{ route('milestones.store') }}">
            @csrf
            <input type="hidden" name="project_id" id="milestone_project_id" value="{{ $project->id }}">
            <input type="hidden" id="milestone_method" name="_method" value="POST">
            
            <h3 class="text-lg font-bold text-gray-900 mb-4" id="milestoneModalTitle">Nieuwe Milestone</h3>
            
            <div class="grid grid-cols-1 gap-4">
                <!-- Title -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Titel <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           name="title" 
                           id="milestone_title"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                           required>
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Beschrijving
                    </label>
                    <textarea name="description" 
                              id="milestone_description"
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
                               id="milestone_start_date"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Einddatum
                        </label>
                        <input type="date" 
                               name="end_date" 
                               id="milestone_end_date"
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
                                id="milestone_fee_type"
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
                                id="milestone_pricing_type"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                required>
                            <option value="fixed_price">Vaste prijs</option>
                            <option value="hourly_rate">Uurtarief</option>
                        </select>
                    </div>
                </div>

                <!-- Budget Fields -->
                <div class="grid grid-cols-3 gap-4">
                    <div id="milestone_budget_field">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Budget
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">€</span>
                            <input type="number" 
                                   name="budget" 
                                   id="milestone_budget"
                                   step="0.01"
                                   min="0"
                                   class="pl-8 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div id="milestone_hourly_rate_field" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Uurtarief
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">€</span>
                            <input type="number" 
                                   name="hourly_rate" 
                                   id="milestone_hourly_rate"
                                   step="0.01"
                                   min="0"
                                   value="75"
                                   class="pl-8 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div id="milestone_hours_field" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Geschatte uren
                        </label>
                        <input type="number" 
                               name="estimated_hours" 
                               id="milestone_estimated_hours"
                               step="0.25"
                               min="0"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>

                <!-- Actual Cost Fields (alleen bij edit) -->
                <div class="grid grid-cols-2 gap-4" id="milestone_actual_fields" style="display: none;">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Werkelijke kosten
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">€</span>
                            <input type="number" 
                                   name="actual_cost" 
                                   id="milestone_actual_cost"
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
                               id="milestone_actual_hours"
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
                              id="milestone_deliverables"
                              rows="3"
                              class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                              placeholder="Een item per regel"></textarea>
                    <p class="mt-1 text-xs text-gray-500">Voer elk deliverable op een nieuwe regel in</p>
                </div>
            </div>

            <!-- Actions -->
            <div class="flex justify-end space-x-2 mt-6">
                <button type="button" 
                        onclick="closeMilestoneModal()"
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
    function closeMilestoneModal() {
        document.getElementById('milestoneModal').classList.add('hidden');
    }

    function toggleMilestonePriceFields() {
        const pricingType = document.getElementById('milestone_pricing_type').value;
        const budgetField = document.getElementById('milestone_budget_field');
        const hourlyRateField = document.getElementById('milestone_hourly_rate_field');
        const hoursField = document.getElementById('milestone_hours_field');
        
        if (pricingType === 'fixed_price') {
            budgetField.style.display = 'block';
            hourlyRateField.style.display = 'none';
            hoursField.style.display = 'none';
        } else {
            budgetField.style.display = 'none';
            hourlyRateField.style.display = 'block';
            hoursField.style.display = 'block';
            
            // Calculate budget when changing hours or rate
            calculateMilestoneBudget();
        }
    }

    function calculateMilestoneBudget() {
        const rate = parseFloat(document.getElementById('milestone_hourly_rate').value) || 0;
        const hours = parseFloat(document.getElementById('milestone_estimated_hours').value) || 0;
        const budget = rate * hours;
        document.getElementById('milestone_budget').value = budget.toFixed(2);
    }

    function editMilestone(milestone) {
        const form = document.getElementById('milestoneForm');
        form.action = `/milestones/${milestone.id}`;
        document.getElementById('milestone_method').value = 'PUT';
        document.getElementById('milestoneModalTitle').textContent = 'Milestone bewerken';
        
        // Fill form
        document.getElementById('milestone_title').value = milestone.title || '';
        document.getElementById('milestone_description').value = milestone.description || '';
        
        // Format dates
        if (milestone.start_date) {
            document.getElementById('milestone_start_date').value = milestone.start_date.split(' ')[0];
        }
        if (milestone.end_date) {
            document.getElementById('milestone_end_date').value = milestone.end_date.split(' ')[0];
        }
        
        document.getElementById('milestone_fee_type').value = milestone.fee_type || 'in_fee';
        document.getElementById('milestone_pricing_type').value = milestone.pricing_type || 'fixed_price';
        document.getElementById('milestone_budget').value = milestone.budget || '';
        document.getElementById('milestone_hourly_rate').value = milestone.hourly_rate || 75;
        document.getElementById('milestone_estimated_hours').value = milestone.estimated_hours || '';
        
        // Show actual fields for editing
        document.getElementById('milestone_actual_fields').style.display = 'grid';
        document.getElementById('milestone_actual_cost').value = milestone.actual_cost || '';
        document.getElementById('milestone_actual_hours').value = milestone.actual_hours || '';
        
        if (milestone.deliverables && milestone.deliverables.length > 0) {
            document.getElementById('milestone_deliverables').value = milestone.deliverables.join('\n');
        }
        
        document.getElementById('milestoneModal').classList.remove('hidden');
        toggleMilestonePriceFields();
    }

    // Event listeners
    document.getElementById('milestone_pricing_type').addEventListener('change', toggleMilestonePriceFields);
    document.getElementById('milestone_hourly_rate').addEventListener('input', calculateMilestoneBudget);
    document.getElementById('milestone_estimated_hours').addEventListener('input', calculateMilestoneBudget);
    
    // Close modal on background click
    document.getElementById('milestoneModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeMilestoneModal();
        }
    });
</script>