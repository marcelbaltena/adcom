<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">🔧 Edit Project</h1>
                        <p class="text-gray-600 mt-1">Update "{{ $project->name }}" project details</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('projects.index') }}" class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition-colors">
                            ← Back to Projects
                        </a>
                        <a href="{{ route('dashboard') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            Dashboard
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Content -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    
                    <!-- Success Message -->
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Error Message -->
                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Edit Form -->
                    <form action="{{ route('projects.update', $project) }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')

                        <!-- Basic Info Section -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">📋 Basic Information</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Project Name -->
                                <div>
                                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Project Name *</label>
                                    <input type="text" 
                                           id="name" 
                                           name="name" 
                                           value="{{ old('name', $project->name) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                           required>
                                    @error('name')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- Status -->
                                <div>
                                    <label for="status" class="block text-sm font-medium text-gray-700 mb-2">Status *</label>
                                    <select id="status" 
                                            name="status" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                            required>
                                        <option value="draft" {{ old('status', $project->status) === 'draft' ? 'selected' : '' }}>📝 Draft</option>
                                        <option value="active" {{ old('status', $project->status) === 'active' ? 'selected' : '' }}>🚀 Active</option>
                                        <option value="completed" {{ old('status', $project->status) === 'completed' ? 'selected' : '' }}>✅ Completed</option>
                                        <option value="cancelled" {{ old('status', $project->status) === 'cancelled' ? 'selected' : '' }}>❌ Cancelled</option>
                                    </select>
                                    @error('status')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <!-- Description -->
                            <div class="mt-6">
                                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                                <textarea id="description" 
                                          name="description" 
                                          rows="4"
                                          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                          placeholder="Describe your project...">{{ old('description', $project->description) }}</textarea>
                                @error('description')
                                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Timeline Section -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">📅 Timeline</h3>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <!-- Start Date -->
                                <div>
                                    <label for="start_date" class="block text-sm font-medium text-gray-700 mb-2">Start Date</label>
                                    <input type="date" 
                                           id="start_date" 
                                           name="start_date" 
                                           value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @error('start_date')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>

                                <!-- End Date -->
                                <div>
                                    <label for="end_date" class="block text-sm font-medium text-gray-700 mb-2">End Date</label>
                                    <input type="date" 
                                           id="end_date" 
                                           name="end_date" 
                                           value="{{ old('end_date', $project->end_date?->format('Y-m-d')) }}"
                                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    @error('end_date')
                                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Budget Section -->
                        <div class="bg-gray-50 p-6 rounded-lg">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4">💰 Budget</h3>
                            
                            <div class="space-y-4">
                                <!-- Budget Type -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Budget Type</label>
                                    <div class="flex space-x-4">
                                        <label class="flex items-center">
                                            <input type="radio" 
                                                   name="budget_type" 
                                                   value="financial" 
                                                   {{ old('budget_type', $project->budget_type ?? 'financial') === 'financial' ? 'checked' : '' }}
                                                   class="text-blue-600">
                                            <span class="ml-2">💰 Financial</span>
                                        </label>
                                        <label class="flex items-center">
                                            <input type="radio" 
                                                   name="budget_type" 
                                                   value="time" 
                                                   {{ old('budget_type', $project->budget_type) === 'time' ? 'checked' : '' }}
                                                   class="text-blue-600">
                                            <span class="ml-2">⏰ Time</span>
                                        </label>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <!-- Budget Amount -->
                                    <div>
                                        <label for="budget" class="block text-sm font-medium text-gray-700 mb-2">
                                            <span id="budget-label">Budget (€)</span>
                                        </label>
                                        <input type="number" 
                                               id="budget" 
                                               name="budget" 
                                               value="{{ old('budget', $project->budget) }}"
                                               min="0" 
                                               step="0.01"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               placeholder="0.00">
                                        @error('budget')
                                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>

                                    <!-- Hourly Rate (for time budget) -->
                                    <div id="hourly-rate-field" style="display: none;">
                                        <label for="hourly_rate" class="block text-sm font-medium text-gray-700 mb-2">Hourly Rate (€)</label>
                                        <input type="number" 
                                               id="hourly_rate" 
                                               name="hourly_rate" 
                                               value="{{ old('hourly_rate', $project->hourly_rate ?? 75) }}"
                                               min="0" 
                                               step="0.01"
                                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
                                               placeholder="75.00">
                                        @error('hourly_rate')
                                            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="flex justify-between items-center pt-6">
                            <button type="button" 
                                    onclick="confirmDelete()" 
                                    class="bg-red-600 hover:bg-red-700 text-white px-6 py-2 rounded-lg transition-colors">
                                🗑️ Delete Project
                            </button>
                            
                            <div class="flex space-x-3">
                                <a href="{{ route('projects.show', $project) }}" 
                                   class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition-colors">
                                    Cancel
                                </a>
                                <button type="submit" 
                                        class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition-colors">
                                    💾 Update Project
                                </button>
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden z-50">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div class="bg-white rounded-lg max-w-md w-full p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🗑️ Delete Project</h3>
                <p class="text-gray-600 mb-6">
                    Are you sure you want to delete "<strong>{{ $project->name }}</strong>"? 
                    This action cannot be undone and will also delete all associated milestones and tasks.
                </p>
                <div class="flex justify-end space-x-3">
                    <button type="button" 
                            onclick="closeDeleteModal()" 
                            class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                        Cancel
                    </button>
                    <form action="{{ route('projects.destroy', $project) }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                            Delete Project
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Budget type toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const budgetTypeInputs = document.querySelectorAll('input[name="budget_type"]');
            const budgetLabel = document.getElementById('budget-label');
            const hourlyRateField = document.getElementById('hourly-rate-field');
            const budgetInput = document.getElementById('budget');

            function toggleBudgetType() {
                const selectedType = document.querySelector('input[name="budget_type"]:checked').value;
                
                if (selectedType === 'time') {
                    budgetLabel.textContent = 'Budget (Hours)';
                    hourlyRateField.style.display = 'block';
                    budgetInput.placeholder = '40';
                } else {
                    budgetLabel.textContent = 'Budget (€)';
                    hourlyRateField.style.display = 'none';
                    budgetInput.placeholder = '5000.00';
                }
            }

            budgetTypeInputs.forEach(input => {
                input.addEventListener('change', toggleBudgetType);
            });

            // Initialize on page load
            toggleBudgetType();
        });

        // Delete modal functions
        function confirmDelete() {
            document.getElementById('deleteModal').classList.remove('hidden');
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('deleteModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeDeleteModal();
            }
        });
    </script>
</x-app-layout>