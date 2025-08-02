<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Project - AdCompro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard.projects') }}" class="text-gray-500 hover:text-gray-700">
                        ← Back to Projects
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">✨ Create New Project</h1>
                </div>
                <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
                    Dashboard
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-8">
            <p class="text-gray-600">Start a new project and organize your work</p>
        </div>

        <!-- Success/Error Messages -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Create Project Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Project Information</h2>
                <p class="text-sm text-gray-600">Fill in the details for your new project</p>
            </div>

            <form action="{{ route('dashboard.store-project') }}" method="POST" class="p-6">
                @csrf
                
                <!-- Basic Information -->
                <div class="mb-8">
                    <h3 class="text-md font-medium text-gray-900 mb-4">📋 Project Information</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Project Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Project Name *
                            </label>
                            <input type="text" id="name" name="name" 
                                   value="{{ old('name') }}"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   placeholder="Enter a descriptive project name..."
                                   required autofocus>
                            <p class="text-xs text-gray-500 mt-1">Choose a clear, descriptive name</p>
                        </div>

                        <!-- Initial Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                Initial Status *
                            </label>
                            <select id="status" name="status" 
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    required>
                                <option value="draft" {{ old('status') == 'draft' ? 'selected' : '' }}>📝 Draft - Initial planning phase</option>
                                <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>🚀 Active - Currently working</option>
                                <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>✅ Completed - Project finished</option>
                                <option value="cancelled" {{ old('status') == 'cancelled' ? 'selected' : '' }}>❌ Cancelled - Project stopped</option>
                            </select>
                            <p class="text-xs text-gray-500 mt-1">Choose the current phase of your project</p>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mt-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Description
                        </label>
                        <textarea id="description" name="description" rows="4"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Describe the project objectives, scope, and key deliverables...">{{ old('description') }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Provide context for team members and stakeholders</p>
                    </div>
                </div>

                <!-- Project Timeline -->
                <div class="mb-8">
                    <h3 class="text-md font-medium text-blue-700 mb-4">📅 Project Timeline</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Start Date -->
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Start Date *
                            </label>
                            <input type="date" id="start_date" name="start_date" 
                                   value="{{ old('start_date') }}"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                            <p class="text-xs text-gray-500 mt-1">When does the project begin?</p>
                        </div>

                        <!-- End Date -->
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                                End Date (Optional)
                            </label>
                            <input type="date" id="end_date" name="end_date" 
                                   value="{{ old('end_date') }}"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Leave empty for ongoing projects</p>
                        </div>
                    </div>
                </div>

                <!-- Project Budget -->
                <div class="mb-8">
                    <h3 class="text-md font-medium text-green-700 mb-4">💰 Project Budget</h3>
                    
                    <!-- Budget Type Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Budget Type *</label>
                        <div class="flex space-x-6">
                            <label class="flex items-center">
                                <input type="radio" name="budget_type" value="financial" 
                                       {{ old('budget_type', 'financial') == 'financial' ? 'checked' : '' }}
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                       onchange="toggleBudgetFields()">
                                <span class="ml-2 text-sm text-gray-700">💰 Financial Budget (€)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="budget_type" value="time" 
                                       {{ old('budget_type') == 'time' ? 'checked' : '' }}
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                       onchange="toggleBudgetFields()">
                                <span class="ml-2 text-sm text-gray-700">⏰ Time Budget (Hours)</span>
                            </label>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Choose whether to track costs or time for this project</p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Budget Amount -->
                        <div>
                            <label for="budget_amount" class="block text-sm font-medium text-gray-700 mb-1">
                                <span id="budget_label">Financial Budget (€)</span> *
                            </label>
                            <div class="relative">
                                <span id="budget_prefix" class="absolute left-3 top-2 text-gray-500 text-sm">€</span>
                                <input type="number" id="budget_amount" name="budget_amount" 
                                       value="{{ old('budget_amount') }}"
                                       class="w-full border border-gray-300 rounded-md pl-8 pr-12 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="0.00"
                                       min="0" step="0.01" required>
                                <span id="budget_suffix" class="absolute right-3 top-2 text-gray-500 text-sm"></span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1" id="budget_help">Total financial budget for the project</p>
                        </div>

                        <!-- Hourly Rate (only for time budget) -->
                        <div id="hourly_rate_field" style="display: {{ old('budget_type') == 'time' ? 'block' : 'none' }};">
                            <label for="hourly_rate" class="block text-sm font-medium text-gray-700 mb-1">
                                Hourly Rate (€/hour)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500 text-sm">€</span>
                                <input type="number" id="hourly_rate" name="hourly_rate" 
                                       value="{{ old('hourly_rate') }}"
                                       class="w-full border border-gray-300 rounded-md pl-8 pr-16 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       placeholder="0.00"
                                       min="0" step="0.01">
                                <span class="absolute right-3 top-2 text-gray-500 text-sm">/hr</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">For budget calculations and reporting</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row justify-between items-center pt-6 border-t border-gray-200 space-y-4 sm:space-y-0">
                    <div class="text-sm text-gray-500">
                        <p>💡 <strong>Tips:</strong> You can edit project details later. Start with basic information and refine as needed.</p>
                    </div>

                    <div class="flex space-x-4">
                        <a href="{{ route('dashboard.projects') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md font-medium transition-colors">
                            🚀 Create Project
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Quick Start Guide -->
        <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-6">
            <h3 class="text-lg font-semibold text-blue-900 mb-2">🚀 What's Next?</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <h4 class="font-medium text-blue-800">1. Create Milestones</h4>
                    <p class="text-blue-700">Break your project into manageable phases</p>
                </div>
                <div>
                    <h4 class="font-medium text-blue-800">2. Add Tasks</h4>
                    <p class="text-blue-700">Define specific work items for each milestone</p>
                </div>
                <div>
                    <h4 class="font-medium text-blue-800">3. Track Progress</h4>
                    <p class="text-blue-700">Monitor completion and stay on schedule</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Budget type toggle functionality
        function toggleBudgetFields() {
            const budgetType = document.querySelector('input[name="budget_type"]:checked').value;
            const budgetLabel = document.getElementById('budget_label');
            const budgetPrefix = document.getElementById('budget_prefix');
            const budgetSuffix = document.getElementById('budget_suffix');
            const budgetHelp = document.getElementById('budget_help');
            const hourlyRateField = document.getElementById('hourly_rate_field');
            const budgetAmountField = document.getElementById('budget_amount');
            
            if (budgetType === 'financial') {
                budgetLabel.textContent = 'Financial Budget (€)';
                budgetPrefix.textContent = '€';
                budgetSuffix.textContent = '';
                budgetHelp.textContent = 'Total financial budget for the project';
                hourlyRateField.style.display = 'none';
                budgetAmountField.placeholder = '5000.00';
            } else {
                budgetLabel.textContent = 'Time Budget (Hours)';
                budgetPrefix.textContent = '';
                budgetSuffix.textContent = 'hrs';
                budgetHelp.textContent = 'Total estimated hours for the project';
                hourlyRateField.style.display = 'block';
                budgetAmountField.placeholder = '160';
            }
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleBudgetFields();
        });
    </script>
</body>
</html>