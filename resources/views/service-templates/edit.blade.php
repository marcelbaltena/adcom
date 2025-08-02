<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Service Template Bewerken
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <form method="POST" action="{{ route('service-templates.update', $serviceTemplate) }}" class="p-6">
                    @csrf
                    @method('PUT')

                    <!-- Name -->
                    <div class="mb-4">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                            Naam <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="name" 
                               id="name" 
                               value="{{ old('name', $serviceTemplate->name) }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('name') border-red-300 @enderror"
                               required>
                        @error('name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-1">
                            Beschrijving
                        </label>
                        <textarea name="description" 
                                  id="description" 
                                  rows="3"
                                  class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('description') border-red-300 @enderror">{{ old('description', $serviceTemplate->description) }}</textarea>
                        @error('description')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Category -->
                    <div class="mb-4">
                        <label for="category" class="block text-sm font-medium text-gray-700 mb-1">
                            Categorie
                        </label>
                        <input type="text" 
                               name="category" 
                               id="category" 
                               value="{{ old('category', $serviceTemplate->category) }}"
                               list="categories"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('category') border-red-300 @enderror"
                               placeholder="Bijv. Marketing, Development, Design">
                        <datalist id="categories">
                            @foreach($categories as $category)
                                <option value="{{ $category }}">
                            @endforeach
                        </datalist>
                        @error('category')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Service Type -->
                    <div class="mb-4">
                        <label for="service_type" class="block text-sm font-medium text-gray-700 mb-1">
                            Type <span class="text-red-500">*</span>
                        </label>
                        <select name="service_type" 
                                id="service_type" 
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('service_type') border-red-300 @enderror"
                                required>
                            <option value="fixed" {{ old('service_type', $serviceTemplate->service_type) == 'fixed' ? 'selected' : '' }}>Vaste prijs</option>
                            <option value="hourly" {{ old('service_type', $serviceTemplate->service_type) == 'hourly' ? 'selected' : '' }}>Uurtarief</option>
                            <option value="package" {{ old('service_type', $serviceTemplate->service_type) == 'package' ? 'selected' : '' }}>Pakket</option>
                        </select>
                        @error('service_type')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Price Fields (conditional) -->
                    <div id="priceFields" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <!-- Base Price -->
                        <div id="basePriceField" style="{{ $serviceTemplate->service_type == 'fixed' ? '' : 'display: none;' }}">
                            <label for="base_price" class="block text-sm font-medium text-gray-700 mb-1">
                                Basis prijs
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">€</span>
                                <input type="number" 
                                       name="base_price" 
                                       id="base_price" 
                                       value="{{ old('base_price', $serviceTemplate->base_price) }}"
                                       step="0.01"
                                       min="0"
                                       class="pl-8 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('base_price') border-red-300 @enderror">
                            </div>
                            @error('base_price')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Hourly Rate -->
                        <div id="hourlyRateField" style="{{ $serviceTemplate->service_type == 'hourly' ? '' : 'display: none;' }}">
                            <label for="hourly_rate" class="block text-sm font-medium text-gray-700 mb-1">
                                Uurtarief
                            </label>
                            <div class="relative">
                                <span class="absolute left-3 top-2 text-gray-500">€</span>
                                <input type="number" 
                                       name="hourly_rate" 
                                       id="hourly_rate" 
                                       value="{{ old('hourly_rate', $serviceTemplate->hourly_rate) }}"
                                       step="0.01"
                                       min="0"
                                       class="pl-8 w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('hourly_rate') border-red-300 @enderror">
                            </div>
                            @error('hourly_rate')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Estimated Hours -->
                        <div id="estimatedHoursField" style="{{ $serviceTemplate->service_type == 'hourly' ? '' : 'display: none;' }}">
                            <label for="estimated_hours" class="block text-sm font-medium text-gray-700 mb-1">
                                Geschatte uren
                            </label>
                            <input type="number" 
                                   name="estimated_hours" 
                                   id="estimated_hours" 
                                   value="{{ old('estimated_hours', $serviceTemplate->estimated_hours) }}"
                                   step="0.25"
                                   min="0"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('estimated_hours') border-red-300 @enderror">
                            @error('estimated_hours')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Tags -->
                    <div class="mb-4">
                        <label for="tags" class="block text-sm font-medium text-gray-700 mb-1">
                            Tags
                        </label>
                        <input type="text" 
                               name="tags" 
                               id="tags" 
                               value="{{ old('tags', $serviceTemplate->tags ? implode(', ', $serviceTemplate->tags) : '') }}"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 @error('tags') border-red-300 @enderror"
                               placeholder="tag1, tag2, tag3">
                        <p class="mt-1 text-xs text-gray-500">Gescheiden door komma's</p>
                        @error('tags')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status Options -->
                    <div class="mb-6 space-y-2">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', $serviceTemplate->is_active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Actief</span>
                        </label>

                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_popular" 
                                   value="1"
                                   {{ old('is_popular', $serviceTemplate->is_popular) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <span class="ml-2 text-sm text-gray-700">Markeer als populair</span>
                        </label>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-between">
                        <button type="button"
                                onclick="if(confirm('Weet je zeker dat je deze service template wilt verwijderen?')) { document.getElementById('delete-form').submit(); }"
                                class="text-red-600 hover:text-red-800 text-sm">
                            Verwijderen
                        </button>

                        <div class="flex items-center space-x-2">
                            <a href="{{ route('service-templates.milestones', $serviceTemplate) }}" 
                               class="px-4 py-2 bg-gray-300 hover:bg-gray-400 text-gray-800 rounded-md transition">
                                Annuleren
                            </a>
                            <button type="submit" 
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition">
                                Wijzigingen opslaan
                            </button>
                        </div>
                    </div>
                </form>

                <!-- Delete form -->
                <form id="delete-form" action="{{ route('service-templates.destroy', $serviceTemplate) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        // Toggle price fields based on service type
        const serviceType = document.getElementById('service_type');
        const basePriceField = document.getElementById('basePriceField');
        const hourlyRateField = document.getElementById('hourlyRateField');
        const estimatedHoursField = document.getElementById('estimatedHoursField');

        function togglePriceFields() {
            const type = serviceType.value;
            
            // Hide all first
            basePriceField.style.display = 'none';
            hourlyRateField.style.display = 'none';
            estimatedHoursField.style.display = 'none';
            
            // Show relevant fields
            if (type === 'fixed') {
                basePriceField.style.display = 'block';
            } else if (type === 'hourly') {
                hourlyRateField.style.display = 'block';
                estimatedHoursField.style.display = 'block';
            }
            // For 'package', price will be calculated from milestones
        }

        serviceType.addEventListener('change', togglePriceFields);
    </script>
    @endpush
</x-app-layout>