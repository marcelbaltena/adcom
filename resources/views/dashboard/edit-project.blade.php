<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Project - AdCompro</title>
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
                    <h1 class="text-2xl font-bold text-gray-900">✏️ Edit Project</h1>
                </div>
                <a href="{{ route('dashboard') }}" class="text-indigo-600 hover:text-indigo-700 font-medium">
                    Dashboard
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
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

        <!-- Edit Project Form -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-lg font-semibold text-gray-900">Edit: {{ $project->name }}</h2>
                <p class="text-sm text-gray-600">Update project details and settings</p>
            </div>

            <form action="{{ route('dashboard.update-project', $project->id) }}" method="POST" class="p-6">
                @csrf
                @method('PUT')
                
                <!-- Basic Information -->
                <div class="mb-8">
                    <h3 class="text-md font-medium text-gray-900 mb-4">📋 Basic Information</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Project Name -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Project Name *
                            </label>
                            <input type="text" id="name" name="name" 
                                   value="{{ old('name', $project->name) }}"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                   required autofocus>
                            <p class="text-xs text-gray-500 mt-1">Choose a clear, descriptive name</p>
                        </div>

                        <!-- Status -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">
                                Project Status *
                            </label>
                            <select id="status" name="status" 
                                    class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                    required>
                                <option value="draft" {{ old('status', $project->status) == 'draft' ? 'selected' : '' }}>📝 Draft</option>
                                <option value="active" {{ old('status', $project->status) == 'active' ? 'selected' : '' }}>🚀 Active</option>
                                <option value="completed" {{ old('status', $project->status) == 'completed' ? 'selected' : '' }}>✅ Completed</option>
                                <option value="cancelled" {{ old('status', $project->status) == 'cancelled' ? 'selected' : '' }}>❌ Cancelled</option>
                            </select>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mt-6">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Project Description
                        </label>
                        <textarea id="description" name="description" rows="4"
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                                  placeholder="Describe the project goals, scope, and requirements...">{{ old('description', $project->description) }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Provide context for team members and stakeholders</p>
                    </div>
                </div>

                <!-- Timeline -->
                <div class="mb-8">
                    <h3 class="text-md font-medium text-blue-700 mb-4">📅 Timeline</h3>
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Start Date -->
                        <div>
                            <label for="start_date" class="block text-sm font-medium text-gray-700 mb-1">
                                Start Date *
                            </label>
                            <input type="date" id="start_date" name="start_date" 
                                   value="{{ old('start_date', $project->start_date ? $project->start_date->format('Y-m-d') : '') }}"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                   required>
                        </div>

                        <!-- End Date -->
                        <div>
                            <label for="end_date" class="block text-sm font-medium text-gray-700 mb-1">
                                End Date (Optional)
                            </label>
                            <input type="date" id="end_date" name="end_date" 
                                   value="{{ old('end_date', $project->end_date ? $project->end_date->format('Y-m-d') : '') }}"
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <p class="text-xs text-gray-500 mt-1">Leave empty for ongoing projects</p>
                        </div>
                    </div>
                </div>

                <!-- Budget Information -->
                <div class="mb-8">
                    <h3 class="text-md font-medium text-green-700 mb-4">💰 Budget Information</h3>
                    
                    <!-- Budget Type Selection -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Budget Type *</label>
                        <div class="flex space-x-6">
                            <label class="flex items-center">
                                <input type="radio" name="budget_type" value="financial" 
                                       {{ old('budget_type', $project->budget_type ?? 'financial') == 'financial' ? 'checked' : '' }}
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                       onchange="toggleBudgetFields()">
                                <span class="ml-2 text-sm text-gray-700">💰 Financial Budget (€)</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="budget_type" value="time" 
                                       {{ old('budget_type', $project->budget_type) == 'time' ? 'checked' : '' }}
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300"
                                       onchange="toggleBudgetFields()">
                                <span class="ml-2 text-sm text-gray-700">⏰ Time Budget (Hours)</span>
                            </label>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- Budget Amount -->
                        <div>
                            <label for="budget_amount" class="block text-sm font-medium text-gray-700 mb-1">
                                <span id="budget_label">Budget Amount</span> *
                            </label>
                            <div class="relative">
                                <span id="budget_prefix" class="absolute left-3 top-2 text-gray-500 text-sm">€</span>
                                <input type="number" id="budget_amount" name="budget_amount" 
                                       value="{{ old('budget_amount', $project->budget) }}"
                                       class="w-full border border-gray-300 rounded-md pl-8 pr-12 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       min="0" step="0.01" required>
                                <span id="budget_suffix" class="absolute right-3 top-2 text-gray-500 text-sm"></span>
                            </div>
                        </div>

                        <!-- Hourly Rate (only for time budget) -->
                        <div id="hourly_rate_field" style="display: {{ old('budget_type', $project->budget_type) == 'time' ? 'block' : 'none' }};">
                            <label for="hourly_rate" class="block text-sm font-medium text-gray-700 mb-1">
                                Hourly Rate (€/hour)
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500 text-sm">€</span>
                                <input type="number" id="hourly_rate" name="hourly_rate" 
                                       value="{{ old('hourly_rate', $project->hourly_rate) }}"
                                       class="w-full border border-gray-300 rounded-md pl-8 pr-16 py-2 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-green-500"
                                       min="0" step="0.01">
                                <span class="absolute right-3 top-2 text-gray-500 text-sm">/hr</span>
                            </div>
                            <p class="text-xs text-gray-500 mt-1">For budget calculations and reporting</p>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row justify-between items-center pt-6 border-t border-gray-200 space-y-4 sm:space-y-0">
                    <!-- Delete Button (Left) -->
                    <button type="button" onclick="confirmDelete()" 
                            class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-md font-medium transition-colors">
                        🗑️ Delete Project
                    </button>

                    <!-- Save/Cancel Buttons (Right) -->
                    <div class="flex space-x-4">
                        <a href="{{ route('dashboard.projects') }}" 
                           class="px-6 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 font-medium transition-colors">
                            Cancel
                        </a>
                        <button type="submit" 
                                class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-md font-medium transition-colors">
                            💾 Update Project
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Project Statistics -->
        <div class="mt-8 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Project Statistics</h3>
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-blue-600">{{ $project->milestones->count() ?? 0 }}</div>
                    <div class="text-sm text-gray-600">Milestones</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ $project->progress ?? 0 }}%</div>
                    <div class="text-sm text-gray-600">Progress</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-purple-600">
                        @if($project->budget_type == 'time')
                            {{ $project->budget ?? 0 }} hrs
                        @else
                            €{{ number_format($project->budget ?? 0, 2) }}
                        @endif
                    </div>
                    <div class="text-sm text-gray-600">Budget</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-orange-600">
                        {{ $project->created_at ? $project->created_at->diffInDays() : 0 }}
                    </div>
                    <div class="text-sm text-gray-600">Days Old</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg shadow-xl max-w-md w-full mx-4">
            <div class="p-6">
                <div class="flex items-center mb-4">
                    <div class="flex-shrink-0 w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <span class="text-red-600 text-xl">⚠️</span>
                    </div>
                    <div class="ml-4">
                        <h3 class="text-lg font-medium text-gray-900">Delete Project</h3>
                        <p class="text-sm text-gray-500">This action cannot be undone</p>
                    </div>
                </div>
                <p class="text-gray-700 mb-6">
                    Are you sure you want to delete "<strong>{{ $project->name }}</strong>"? 
                    This will also delete all associated milestones, tasks, and subtasks.
                </p>
                <div class="flex justify-end space-x-4">
                    <button type="button" onclick="closeDeleteModal()" 
                            class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                        Cancel
                    </button>
                    <form action="{{ route('dashboard.delete-project', $project->id) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-md">
                            Delete Project
                        </button>
                    </form>
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
            const hourlyRateField = document.getElementById('hourly_rate_field');
            const budgetAmountField = document.getElementById('budget_amount');
            
            if (budgetType === 'financial') {
                budgetLabel.textContent = 'Financial Budget';
                budgetPrefix.textContent = '€';
                budgetSuffix.textContent = '';
                hourlyRateField.style.display = 'none';
                budgetAmountField.placeholder = 'e.g., 5000';
            } else {
                budgetLabel.textContent = 'Time Budget';
                budgetPrefix.textContent = '';
                budgetSuffix.textContent = 'hrs';
                hourlyRateField.style.display = 'block';
                budgetAmountField.placeholder = 'e.g., 40';
            }
        }

        // Delete confirmation modal
        function confirmDelete() {
            document.getElementById('deleteModal').classList.remove('hidden');
            document.getElementById('deleteModal').classList.add('flex');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
            document.getElementById('deleteModal').classList.remove('flex');
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function() {
            toggleBudgetFields();
        });
    </script>
</body>
</html>