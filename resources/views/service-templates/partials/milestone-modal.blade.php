<!-- Milestone Modal -->
<div id="milestoneModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <form id="milestoneForm" method="POST" action="{{ route('milestone-templates.store') }}">
            @csrf
            <input type="hidden" name="service_template_id" value="{{ $serviceTemplate->id }}">
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
                            Standaard startdatum
                        </label>
                        <input type="date" 
                               name="default_start_date" 
                               id="milestone_default_start_date"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">Wordt gebruikt als default bij toevoegen aan project</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Standaard einddatum
                        </label>
                        <input type="date" 
                               name="default_end_date" 
                               id="milestone_default_end_date"
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
                                id="milestone_fee_type"
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
                                id="milestone_pricing_type"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                required>
                            <option value="fixed_price">Vaste prijs</option>
                            <option value="hourly_rate">Uurtarief</option>
                        </select>
                    </div>
                </div>

                <!-- Price Fields -->
                <div class="grid grid-cols-3 gap-4">
                    <div id="milestone_price_field">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Prijs
                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">€</span>
                            <input type="number" 
                                   name="price" 
                                   id="milestone_price"
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
                        id="cancelMilestoneBtn"
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
    
    // Close modal on background click
    milestoneModal.addEventListener('click', function(e) {
        if (e.target === this) {
            closeMilestoneModal();
        }
    });
});
</script>