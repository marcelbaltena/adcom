{{-- resources/views/companies/edit.blade.php --}}
<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Edit Company</h1>
                        <p class="text-gray-600 mt-2">Update company information and settings</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('companies.show', $company) }}" 
                           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-150">
                            ← Back to Company
                        </a>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <form action="{{ route('companies.update', $company) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')

                    <!-- Success Message -->
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- Basic Information -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                            Basic Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Company Name *
                                </label>
                                <input type="text" id="name" name="name" 
                                       value="{{ old('name', $company->name) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror"
                                       required>
                                @error('name')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="is_active" class="block text-sm font-medium text-gray-700 mb-2">
                                    Status
                                </label>
                                <select id="is_active" name="is_active" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="1" {{ old('is_active', $company->is_active) ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ !old('is_active', $company->is_active) ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea id="description" name="description" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Brief description of the company...">{{ old('description', $company->description) }}</textarea>
                        </div>
                    </div>

                    <!-- Contact Information -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                            Contact Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Email
                                </label>
                                <input type="email" id="email" name="email" 
                                       value="{{ old('email', $company->email) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror">
                                @error('email')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone
                                </label>
                                <input type="text" id="phone" name="phone" 
                                       value="{{ old('phone', $company->phone) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="website" class="block text-sm font-medium text-gray-700 mb-2">
                                    Website
                                </label>
                                <input type="url" id="website" name="website" 
                                       value="{{ old('website', $company->website) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="https://example.com">
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                            Address Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="md:col-span-2">
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                    Street Address
                                </label>
                                <input type="text" id="address" name="address" 
                                       value="{{ old('address', $company->address) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                                    City
                                </label>
                                <input type="text" id="city" name="city" 
                                       value="{{ old('city', $company->city) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">
                                    Postal Code
                                </label>
                                <input type="text" id="postal_code" name="postal_code" 
                                       value="{{ old('postal_code', $company->postal_code) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                                    Country
                                </label>
                                <select id="country" name="country" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Country</option>
                                    <option value="Netherlands" {{ old('country', $company->country) === 'Netherlands' ? 'selected' : '' }}>Netherlands</option>
                                    <option value="Belgium" {{ old('country', $company->country) === 'Belgium' ? 'selected' : '' }}>Belgium</option>
                                    <option value="Germany" {{ old('country', $company->country) === 'Germany' ? 'selected' : '' }}>Germany</option>
                                    <option value="France" {{ old('country', $company->country) === 'France' ? 'selected' : '' }}>France</option>
                                    <option value="United Kingdom" {{ old('country', $company->country) === 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Business Information -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                            Business Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="kvk_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    KvK Number
                                </label>
                                <input type="text" id="kvk_number" name="kvk_number" 
                                       value="{{ old('kvk_number', $company->kvk_number) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('kvk_number') border-red-500 @enderror"
                                       placeholder="12345678">
                                @error('kvk_number')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="vat_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    VAT Number
                                </label>
                                <input type="text" id="vat_number" name="vat_number" 
                                       value="{{ old('vat_number', $company->vat_number) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="NL123456789B01">
                            </div>
                            
                            <div class="md:col-span-2">
                                <label for="iban" class="block text-sm font-medium text-gray-700 mb-2">
                                    IBAN
                                </label>
                                <input type="text" id="iban" name="iban" 
                                       value="{{ old('iban', $company->iban) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
                                       placeholder="NL91 ABNA 0417 1643 00">
                            </div>
                        </div>
                    </div>

                    <!-- Settings -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                            Settings
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">
                                    Currency
                                </label>
                                <select id="currency" name="currency" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="EUR" {{ old('currency', $company->currency) === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                    <option value="USD" {{ old('currency', $company->currency) === 'USD' ? 'selected' : '' }}>USD ($)</option>
                                    <option value="GBP" {{ old('currency', $company->currency) === 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="timezone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Timezone
                                </label>
                                <select id="timezone" name="timezone" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="Europe/Amsterdam" {{ old('timezone', $company->timezone) === 'Europe/Amsterdam' ? 'selected' : '' }}>Europe/Amsterdam</option>
                                    <option value="Europe/London" {{ old('timezone', $company->timezone) === 'Europe/London' ? 'selected' : '' }}>Europe/London</option>
                                    <option value="Europe/Berlin" {{ old('timezone', $company->timezone) === 'Europe/Berlin' ? 'selected' : '' }}>Europe/Berlin</option>
                                    <option value="America/New_York" {{ old('timezone', $company->timezone) === 'America/New_York' ? 'selected' : '' }}>America/New_York</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="default_hourly_rate" class="block text-sm font-medium text-gray-700 mb-2">
                                    Default Hourly Rate
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">€</span>
                                    <input type="number" id="default_hourly_rate" name="default_hourly_rate" 
                                           value="{{ old('default_hourly_rate', $company->default_hourly_rate) }}"
                                           step="0.01" min="0"
                                           class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0.00">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                        <div class="flex space-x-3">
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-150">
                                💾 Update Company
                            </button>
                            <a href="{{ route('companies.show', $company) }}" 
                               class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-150">
                                Cancel
                            </a>
                        </div>
                        
                        <button type="button" 
                                onclick="if(confirm('Are you sure you want to delete this company? This action cannot be undone.')) { document.getElementById('delete-form').submit(); }"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-150">
                            🗑️ Delete Company
                        </button>
                    </div>
                </form>

                <!-- Hidden Delete Form -->
                <form id="delete-form" action="{{ route('companies.destroy', $company) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

        </div>
    </div>
</x-app-layout>