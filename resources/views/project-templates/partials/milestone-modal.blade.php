<!-- Milestone Modal -->
<div id="milestoneModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full z-50">
    <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-md bg-white">
        <form id="milestoneForm" method="POST" action="{{ route('project-milestones.store') }}">
            @csrf
            <input type="hidden" name="project_template_id" id="milestone_project_template_id" value="{{ $projectTemplate->id }}">
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

                <!-- Planning Fields voor Project Templates -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Start op dag <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               name="days_from_start" 
                               id="milestone_days_from_start"
                               min="0"
                               value="0"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                               required>
                        <p class="mt-1 text-xs text-gray-500">Aantal dagen vanaf project start</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Duur in dagen <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               name="duration_days" 
                               id="milestone_duration_days"
                               min="1"
                               value="1"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                               required>
                        <p class="mt-1 text-xs text-gray-500">Hoeveel dagen duurt deze milestone</p>
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

                <!-- Estimated Hours -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">
                        Geschatte uren
                    </label>
                    <input type="number" 
                           name="estimated_hours" 
                           id="milestone_estimated_hours"
                           step="0.25"
                           min="0"
                           class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <p class="mt-1 text-xs text-gray-500">Dit wordt gebruikt voor planning, prijzen worden pas bij project bepaald</p>
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
    function toggleMilestonePriceFields() {
        // Voor project templates tonen we geen prijzen
    }

    function editMilestone(milestone) {
        const form = document.getElementById('milestoneForm');
        form.action = `/project-milestones/${milestone.id}`;
        document.getElementById('milestone_method').value = 'PUT';
        document.getElementById('milestoneModalTitle').textContent = 'Milestone bewerken';
        
        // Fill form
        document.getElementById('milestone_title').value = milestone.title || '';
        document.getElementById('milestone_description').value = milestone.description || '';
        document.getElementById('milestone_days_from_start').value = milestone.days_from_start || 0;
        document.getElementById('milestone_duration_days').value = milestone.duration_days || 1;
        document.getElementById('milestone_fee_type').value = milestone.fee_type || 'in_fee';
        document.getElementById('milestone_pricing_type').value = milestone.pricing_type || 'fixed_price';
        document.getElementById('milestone_estimated_hours').value = milestone.estimated_hours || '';
        
        if (milestone.deliverables && milestone.deliverables.length > 0) {
            document.getElementById('milestone_deliverables').value = milestone.deliverables.join('\n');
        }
        
        document.getElementById('milestoneModal').classList.remove('hidden');
    }
</script>