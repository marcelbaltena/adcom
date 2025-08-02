<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Create New Project</h1>
                        <p class="text-gray-600 mt-2">Set up a new project with budget tracking</p>
                    </div>
                    <a href="{{ route('projects.index') }}" class="bg-gray-500 hover:bg-gray-600 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                        Back to Projects
                    </a>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-white overflow-hidden shadow-lg rounded-lg">
                <form action="{{ route('projects.store') }}" method="POST" class="p-6 space-y-6">
                    @csrf

                    <!-- Display Errors -->
                    @if ($errors->any())
                        <div class="bg-red-50 border-l-4 border-red-500 p-4">
                            <div class="flex">
                                <div class="flex-shrink-0">
                                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-red-800">There were errors with your submission</h3>
                                    <div class="mt-2 text-sm text-red-700">
                                        <ul class="list-disc space-y-1 pl-5">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Project Template Section - NIEUW -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Project Template (Optional)</h3>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Project Template Selection -->
                            <div>
                                <label for="project_template_id" class="block text-sm font-medium text-gray-700">Use Project Template</label>
                                <select name="project_template_id" 
                                        id="project_template_id" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        onchange="toggleTemplateOptions(this)">
                                    <option value="">-- No template --</option>
                                    @php
                                        $templates = \App\Models\ProjectTemplate::where('is_active', true)
                                            ->with('milestones')
                                            ->orderBy('category')
                                            ->orderBy('name')
                                            ->get();
                                    @endphp
                                    @foreach($templates as $template)
                                        <option value="{{ $template->id }}" 
                                                data-milestones="{{ $template->milestones->count() }}"
                                                data-days="{{ $template->total_days }}"
                                                data-hours="{{ $template->getTotalEstimatedHours() }}"
                                                data-description="{{ $template->description }}"
                                                {{ old('project_template_id') == $template->id ? 'selected' : '' }}>
                                            {{ $template->name }}
                                            @if($template->category)
                                                ({{ $template->category }})
                                            @endif
                                            - {{ $template->milestones->count() }} milestones, {{ $template->total_days }} days
                                        </option>
                                    @endforeach
                                </select>
                                <p class="mt-1 text-sm text-gray-500">Select a template to automatically add milestones to your project</p>
                            </div>

                            <!-- Template Details (hidden by default) -->
                            <div id="templateDetails" class="hidden bg-blue-50 rounded-md p-4">
                                <h4 class="font-medium text-blue-900 mb-2">Template Details:</h4>
                                <div class="grid grid-cols-3 gap-4 text-sm">
                                    <div>
                                        <span class="text-blue-700">Milestones:</span>
                                        <span id="detailMilestones" class="font-medium text-blue-900 ml-1">-</span>
                                    </div>
                                    <div>
                                        <span class="text-blue-700">Duration:</span>
                                        <span id="detailDays" class="font-medium text-blue-900 ml-1">-</span> days
                                    </div>
                                    <div>
                                        <span class="text-blue-700">Estimated hours:</span>
                                        <span id="detailHours" class="font-medium text-blue-900 ml-1">-</span> hours
                                    </div>
                                </div>
                                <div id="detailDescription" class="mt-2 text-sm text-blue-800"></div>
                            </div>

                            <!-- Template Start Date (hidden by default) -->
                            <div id="templateStartDateField" class="hidden">
                                <label for="template_start_date" class="block text-sm font-medium text-gray-700">
                                    Template Planning Start Date *
                                    <span class="text-xs text-gray-500">(Milestones will be scheduled from this date)</span>
                                </label>
                                <input type="date" 
                                       name="template_start_date" 
                                       id="template_start_date" 
                                       value="{{ old('template_start_date', date('Y-m-d')) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                       onchange="calculateTemplateEndDate()">
                                <div id="templateEndDateInfo" class="mt-2 text-sm text-gray-600"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Basic Information</h3>
                        
                        <div class="grid grid-cols-1 gap-6">
                            <!-- Project Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700">Project Name *</label>
                                <input type="text" 
                                       name="name" 
                                       id="name" 
                                       value="{{ old('name') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-300 @enderror"
                                       required>
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-gray-700">Description</label>
                                <textarea name="description" 
                                          id="description" 
                                          rows="3" 
                                          class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('description') border-red-300 @enderror">{{ old('description') }}</textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Customer & Company Information -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Customer & Company</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Customer -->
                            <div>
                                <label for="customer_id" class="block text-sm font-medium text-gray-700">Customer *</label>
                                <select name="customer_id" 
                                        id="customer_id" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('customer_id') border-red-300 @enderror"
                                        required
                                        onchange="updateBillingCompany(this)">
                                    <option value="">Select Customer</option>
                                    @foreach($customers as $customer)
                                        <option value="{{ $customer->id }}" 
                                                data-company-id="{{ $customer->company_id }}"
                                                data-billing-company-id="{{ $customer->billing_company_id }}"
                                                {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                            {{ $customer->name }} 
                                            @if($customer->company)
                                                ({{ $customer->company->name }})
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                                @error('customer_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Billing Company -->
                            <div>
                                <label for="billing_company_id" class="block text-sm font-medium text-gray-700">Billing Company *</label>
                                <select name="billing_company_id" 
                                        id="billing_company_id" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('billing_company_id') border-red-300 @enderror"
                                        required>
                                    <option value="">Select Billing Company</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" 
                                                {{ old('billing_company_id') == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('billing_company_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Created By Company (Hidden) -->
                            <input type="hidden" name="created_by_company_id" id="created_by_company_id" value="{{ old('created_by_company_id') }}">
                        </div>
                    </div>

                    <!-- Project Details -->
                    <div class="border-b border-gray-200 pb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Project Details</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Project Manager -->
                            <div>
                                <label for="user_id" class="block text-sm font-medium text-gray-700">Project Manager *</label>
                                <select name="user_id" 
                                        id="user_id" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('user_id') border-red-300 @enderror"
                                        required>
                                    <option value="">Select Manager</option>
                                    @foreach($users as $user)
                                        <option value="{{ $user->id }}" 
                                                {{ old('user_id', Auth::id()) == $user->id ? 'selected' : '' }}>
                                            {{ $user->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('user_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status *</label>
                                <select name="status" 
                                        id="status" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('status') border-red-300 @enderror"
                                        required>
                                    <option value="planning" {{ old('status', 'planning') == 'planning' ? 'selected' : '' }}>Planning</option>
                                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                                    <option value="on_hold" {{ old('status') == 'on_hold' ? 'selected' : '' }}>On Hold</option>
                                    <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                @error('status')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Start Date -->
                            <div>
                                <label for="start_date" class="block text-sm font-medium text-gray-700">Start Date *</label>
                                <input type="date" 
                                       name="start_date" 
                                       id="start_date" 
                                       value="{{ old('start_date', date('Y-m-d')) }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('start_date') border-red-300 @enderror"
                                       required>
                                @error('start_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- End Date -->
                            <div id="endDateContainer">
                                <label for="end_date" class="block text-sm font-medium text-gray-700">End Date</label>
                                <input type="date" 
                                       name="end_date" 
                                       id="end_date" 
                                       value="{{ old('end_date') }}"
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('end_date') border-red-300 @enderror">
                                @error('end_date')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Source -->
                            <div>
                                <label for="source" class="block text-sm font-medium text-gray-700">Source</label>
                                <select name="source" 
                                        id="source" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('source') border-red-300 @enderror">
                                    <option value="direct" {{ old('source', 'direct') == 'direct' ? 'selected' : '' }}>Direct</option>
                                    <option value="referral" {{ old('source') == 'referral' ? 'selected' : '' }}>Referral</option>
                                    <option value="marketing" {{ old('source') == 'marketing' ? 'selected' : '' }}>Marketing</option>
                                    <option value="existing_customer" {{ old('source') == 'existing_customer' ? 'selected' : '' }}>Existing Customer</option>
                                </select>
                                @error('source')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Budget Information -->
                    <div class="pb-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Budget Information</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Budget -->
                            <div>
                                <label for="budget" class="block text-sm font-medium text-gray-700">Budget *</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm" id="currency-symbol">€</span>
                                    </div>
                                    <input type="number" 
                                           name="budget" 
                                           id="budget" 
                                           value="{{ old('budget', '1000') }}"
                                           min="0" 
                                           step="0.01" 
                                           class="mt-1 block w-full pl-8 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('budget') border-red-300 @enderror"
                                           required>
                                </div>
                                @error('budget')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Currency -->
                            <div>
                                <label for="currency" class="block text-sm font-medium text-gray-700">Currency *</label>
                                <select name="currency" 
                                        id="currency" 
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('currency') border-red-300 @enderror"
                                        required
                                        onchange="updateCurrencySymbol(this)">
                                    <option value="EUR" {{ old('currency', 'EUR') == 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                    <option value="USD" {{ old('currency') == 'USD' ? 'selected' : '' }}>USD ($)</option>
                                    <option value="GBP" {{ old('currency') == 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                </select>
                                @error('currency')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Project Value -->
                            <div>
                                <label for="project_value" class="block text-sm font-medium text-gray-700">Project Value</label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm" id="value-currency-symbol">€</span>
                                    </div>
                                    <input type="number" 
                                           name="project_value" 
                                           id="project_value" 
                                           value="{{ old('project_value') }}"
                                           min="0" 
                                           step="0.01" 
                                           class="mt-1 block w-full pl-8 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('project_value') border-red-300 @enderror"
                                           placeholder="Leave empty to use budget amount">
                                </div>
                                @error('project_value')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Budget Tolerance -->
                            <div>
                                <label for="budget_tolerance_percentage" class="block text-sm font-medium text-gray-700">
                                    Budget Tolerance % *
                                    <span class="text-xs text-gray-500">(How much over budget is acceptable)</span>
                                </label>
                                <input type="number" 
                                       name="budget_tolerance_percentage" 
                                       id="budget_tolerance_percentage" 
                                       value="{{ old('budget_tolerance_percentage', '10') }}"
                                       min="0" 
                                       max="100" 
                                       step="1" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('budget_tolerance_percentage') border-red-300 @enderror"
                                       required>
                                @error('budget_tolerance_percentage')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Budget Warning -->
                            <div>
                                <label for="budget_warning_percentage" class="block text-sm font-medium text-gray-700">
                                    Budget Warning % *
                                    <span class="text-xs text-gray-500">(Alert when this % from budget limit)</span>
                                </label>
                                <input type="number" 
                                       name="budget_warning_percentage" 
                                       id="budget_warning_percentage" 
                                       value="{{ old('budget_warning_percentage', '5') }}"
                                       min="0" 
                                       max="100" 
                                       step="1" 
                                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('budget_warning_percentage') border-red-300 @enderror"
                                       required>
                                @error('budget_warning_percentage')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="{{ route('projects.index') }}" 
                           class="bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium py-2 px-4 rounded-lg transition-colors">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg transition-colors">
                            Create Project
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script>
        // Toggle template options - AANGEPAST
        function toggleTemplateOptions(select) {
            const templateDetails = document.getElementById('templateDetails');
            const startDateField = document.getElementById('templateStartDateField');
            const endDateInput = document.getElementById('end_date');
            const endDateContainer = document.getElementById('endDateContainer');
            
            if (select.value) {
                const option = select.selectedOptions[0];
                
                // Show template details
                document.getElementById('detailMilestones').textContent = option.dataset.milestones || '0';
                document.getElementById('detailDays').textContent = option.dataset.days || '0';
                document.getElementById('detailHours').textContent = parseFloat(option.dataset.hours || 0).toFixed(1);
                document.getElementById('detailDescription').textContent = option.dataset.description || '';
                
                templateDetails.classList.remove('hidden');
                startDateField.classList.remove('hidden');
                
                // Clear end date when template is selected
                endDateInput.value = '';
                
                // Add info message about automatic end date
                let infoDiv = document.getElementById('endDateAutoInfo');
                if (!infoDiv) {
                    infoDiv = document.createElement('div');
                    infoDiv.id = 'endDateAutoInfo';
                    infoDiv.className = 'mt-1 text-sm text-blue-600';
                    infoDiv.innerHTML = '<i class="fas fa-info-circle mr-1"></i>End date will be calculated automatically based on template duration';
                    endDateContainer.appendChild(infoDiv);
                }
                
                // Auto-fill template start date with project start date
                const projectStartDate = document.getElementById('start_date').value;
                if (projectStartDate) {
                    document.getElementById('template_start_date').value = projectStartDate;
                    calculateTemplateEndDate();
                }
            } else {
                templateDetails.classList.add('hidden');
                startDateField.classList.add('hidden');
                
                // Remove info message
                const infoDiv = document.getElementById('endDateAutoInfo');
                if (infoDiv) {
                    infoDiv.remove();
                }
            }
        }

        // Calculate template end date - AANGEPAST
        function calculateTemplateEndDate() {
            const templateSelect = document.getElementById('project_template_id');
            const startDateInput = document.getElementById('template_start_date');
            const endDateInfo = document.getElementById('templateEndDateInfo');
            
            if (templateSelect.value && startDateInput.value) {
                const option = templateSelect.selectedOptions[0];
                const days = parseInt(option.dataset.days) || 0;
                const startDate = new Date(startDateInput.value);
                const endDate = new Date(startDate);
                endDate.setDate(endDate.getDate() + days);
                
                const formattedDate = endDate.toLocaleDateString('en-US', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                
                endDateInfo.innerHTML = `<i class="fas fa-info-circle text-blue-500 mr-1"></i>Estimated end date based on template: <strong>${formattedDate}</strong>`;
                
                // Always update project end date when template is selected
                const projectEndDate = document.getElementById('end_date');
                projectEndDate.value = endDate.toISOString().split('T')[0];
            }
        }

        // Update billing company when customer is selected
        function updateBillingCompany(select) {
            const selectedOption = select.options[select.selectedIndex];
            const billingCompanyId = selectedOption.getAttribute('data-billing-company-id');
            const companyId = selectedOption.getAttribute('data-company-id');
            
            if (billingCompanyId) {
                document.getElementById('billing_company_id').value = billingCompanyId;
            }
            
            // Also set created_by_company_id
            if (companyId) {
                document.getElementById('created_by_company_id').value = companyId;
            }
        }

        // Update currency symbol
        function updateCurrencySymbol(select) {
            const symbols = {
                'EUR': '€',
                'USD': '$',
                'GBP': '£'
            };
            const symbol = symbols[select.value] || '€';
            document.getElementById('currency-symbol').textContent = symbol;
            document.getElementById('value-currency-symbol').textContent = symbol;
        }

        // Copy project start date to template start date
        document.getElementById('start_date').addEventListener('change', function() {
            const templateField = document.getElementById('template_start_date');
            if (document.getElementById('project_template_id').value) {
                templateField.value = this.value;
                calculateTemplateEndDate();
            }
        });

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            // Set initial currency symbol
            const currencySelect = document.getElementById('currency');
            if (currencySelect) {
                updateCurrencySymbol(currencySelect);
            }
            
            // Check if template was selected (old value)
            const templateSelect = document.getElementById('project_template_id');
            if (templateSelect && templateSelect.value) {
                toggleTemplateOptions(templateSelect);
            }
        });

        // Debug form submission
        document.querySelector('form').addEventListener('submit', function(e) {
            const templateId = document.getElementById('project_template_id').value;
            console.log('Form submitting with template:', templateId);
            if (templateId) {
                console.log('Template start date:', document.getElementById('template_start_date').value);
            }
        });
    </script>
</x-app-layout>