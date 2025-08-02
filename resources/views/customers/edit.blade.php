<x-app-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Edit Customer</h1>
                        <p class="text-gray-600 mt-2">Update customer information and settings</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('customers.show', $customer) }}" 
                           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-150">
                            ← Back to Customer
                        </a>
                    </div>
                </div>
            </div>

            <!-- Edit Form -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <form action="{{ route('customers.update', $customer) }}" method="POST" class="p-6">
                    @csrf
                    @method('PUT')

                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                            {{ session('error') }}
                        </div>
                    @endif

                    <!-- Basic Information -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                            Basic Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="company_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Company *
                                </label>
                                <select id="company_id" name="company_id" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('company_id') border-red-500 @enderror"
                                        required>
                                    <option value="">Select Company</option>
                                    @foreach($companies as $company)
                                        <option value="{{ $company->id }}" {{ old('company_id', $customer->company_id) == $company->id ? 'selected' : '' }}>
                                            {{ $company->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('company_id')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                            
                            <div>
                                <label for="type" class="block text-sm font-medium text-gray-700 mb-2">
                                    Customer Type *
                                </label>
                                <select id="type" name="type" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('type') border-red-500 @enderror"
                                        required onchange="toggleBusinessFields()">
                                    <option value="company" {{ old('type', $customer->type) === 'company' ? 'selected' : '' }}>Company</option>
                                    <option value="individual" {{ old('type', $customer->type) === 'individual' ? 'selected' : '' }}>Individual</option>
                                </select>
                                @error('type')
                                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div>
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                                    Name *
                                </label>
                                <input type="text" id="name" name="name" 
                                       value="{{ old('name', $customer->name) }}"
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
                                    <option value="1" {{ old('is_active', $customer->is_active) ? 'selected' : '' }}>Active</option>
                                    <option value="0" {{ !old('is_active', $customer->is_active) ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6">
                            <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                                Description
                            </label>
                            <textarea id="description" name="description" rows="3"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Brief description of the customer...">{{ old('description', $customer->description) }}</textarea>
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
                                       value="{{ old('email', $customer->email) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Phone
                                </label>
                                <input type="text" id="phone" name="phone" 
                                       value="{{ old('phone', $customer->phone) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
                            <div>
                                <label for="website" class="block text-sm font-medium text-gray-700 mb-2">
                                    Website
                                </label>
                                <input type="url" id="website" name="website" 
                                       value="{{ old('website', $customer->website) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="https://example.com">
                            </div>
                            
                            <div class="contact-person-section">
                                <label for="contact_person" class="block text-sm font-medium text-gray-700 mb-2">
                                    Contact Person
                                </label>
                                <input type="text" id="contact_person" name="contact_person" 
                                       value="{{ old('contact_person', $customer->contact_person) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6 contact-person-section">
                            <div>
                                <label for="contact_email" class="block text-sm font-medium text-gray-700 mb-2">
                                    Contact Email
                                </label>
                                <input type="email" id="contact_email" name="contact_email" 
                                       value="{{ old('contact_email', $customer->contact_email) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div>
                                <label for="contact_phone" class="block text-sm font-medium text-gray-700 mb-2">
                                    Contact Phone
                                </label>
                                <input type="text" id="contact_phone" name="contact_phone" 
                                       value="{{ old('contact_phone', $customer->contact_phone) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                            Address Information
                        </h3>
                        <div class="grid grid-cols-1 gap-6">
                            <div>
                                <label for="address" class="block text-sm font-medium text-gray-700 mb-2">
                                    Street Address
                                </label>
                                <input type="text" id="address" name="address" 
                                       value="{{ old('address', $customer->address) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                                <div>
                                    <label for="city" class="block text-sm font-medium text-gray-700 mb-2">
                                        City
                                    </label>
                                    <input type="text" id="city" name="city" 
                                           value="{{ old('city', $customer->city) }}"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <div>
                                    <label for="postal_code" class="block text-sm font-medium text-gray-700 mb-2">
                                        Postal Code
                                    </label>
                                    <input type="text" id="postal_code" name="postal_code" 
                                           value="{{ old('postal_code', $customer->postal_code) }}"
                                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                </div>
                                
                                <div>
                                    <label for="country" class="block text-sm font-medium text-gray-700 mb-2">
                                        Country
                                    </label>
                                    <select id="country" name="country" 
                                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                        <option value="">Select Country</option>
                                        <option value="Netherlands" {{ old('country', $customer->country) === 'Netherlands' ? 'selected' : '' }}>Netherlands</option>
                                        <option value="Belgium" {{ old('country', $customer->country) === 'Belgium' ? 'selected' : '' }}>Belgium</option>
                                        <option value="Germany" {{ old('country', $customer->country) === 'Germany' ? 'selected' : '' }}>Germany</option>
                                        <option value="France" {{ old('country', $customer->country) === 'France' ? 'selected' : '' }}>France</option>
                                        <option value="United Kingdom" {{ old('country', $customer->country) === 'United Kingdom' ? 'selected' : '' }}>United Kingdom</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Business Information (for companies) -->
                    <div class="mb-8 business-fields">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                            Business Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="kvk_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    KvK Number
                                </label>
                                <input type="text" id="kvk_number" name="kvk_number" 
                                       value="{{ old('kvk_number', $customer->kvk_number) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="12345678">
                            </div>
                            
                            <div>
                                <label for="vat_number" class="block text-sm font-medium text-gray-700 mb-2">
                                    VAT Number
                                </label>
                                <input type="text" id="vat_number" name="vat_number" 
                                       value="{{ old('vat_number', $customer->vat_number) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="NL123456789B01">
                            </div>
                            
                            <div>
                                <label for="iban" class="block text-sm font-medium text-gray-700 mb-2">
                                    IBAN
                                </label>
                                <input type="text" id="iban" name="iban" 
                                       value="{{ old('iban', $customer->iban) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 font-mono"
                                       placeholder="NL91 ABNA 0417 1643 00">
                            </div>
                            
                            <div class="business-fields">
                                <label for="industry" class="block text-sm font-medium text-gray-700 mb-2">
                                    Industry
                                </label>
                                <input type="text" id="industry" name="industry" 
                                       value="{{ old('industry', $customer->industry) }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                       placeholder="e.g., Technology, Healthcare">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6 business-fields">
                            <div>
                                <label for="size" class="block text-sm font-medium text-gray-700 mb-2">
                                    Company Size
                                </label>
                                <select id="size" name="size" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Select Size</option>
                                    <option value="small" {{ old('size', $customer->size) === 'small' ? 'selected' : '' }}>Small (1-10 employees)</option>
                                    <option value="medium" {{ old('size', $customer->size) === 'medium' ? 'selected' : '' }}>Medium (11-50 employees)</option>
                                    <option value="large" {{ old('size', $customer->size) === 'large' ? 'selected' : '' }}>Large (51-250 employees)</option>
                                    <option value="enterprise" {{ old('size', $customer->size) === 'enterprise' ? 'selected' : '' }}>Enterprise (250+ employees)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Financial Settings -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                            Financial Settings
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <label for="currency" class="block text-sm font-medium text-gray-700 mb-2">
                                    Currency *
                                </label>
                                <select id="currency" name="currency" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required>
                                    <option value="EUR" {{ old('currency', $customer->currency) === 'EUR' ? 'selected' : '' }}>EUR (€)</option>
                                    <option value="USD" {{ old('currency', $customer->currency) === 'USD' ? 'selected' : '' }}>USD ($)</option>
                                    <option value="GBP" {{ old('currency', $customer->currency) === 'GBP' ? 'selected' : '' }}>GBP (£)</option>
                                </select>
                            </div>
                            
                            <div>
                                <label for="default_hourly_rate" class="block text-sm font-medium text-gray-700 mb-2">
                                    Default Hourly Rate
                                </label>
                                <div class="relative">
                                    <span class="absolute left-3 top-2 text-gray-500">€</span>
                                    <input type="number" id="default_hourly_rate" name="default_hourly_rate" 
                                           value="{{ old('default_hourly_rate', $customer->default_hourly_rate) }}"
                                           step="0.01" min="0"
                                           class="w-full pl-8 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                           placeholder="0.00">
                                </div>
                            </div>
                            
                            <div>
                                <label for="payment_terms" class="block text-sm font-medium text-gray-700 mb-2">
                                    Payment Terms *
                                </label>
                                <select id="payment_terms" name="payment_terms" 
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                        required>
                                    <option value="7" {{ old('payment_terms', $customer->payment_terms) === '7' ? 'selected' : '' }}>7 days</option>
                                    <option value="14" {{ old('payment_terms', $customer->payment_terms) === '14' ? 'selected' : '' }}>14 days</option>
                                    <option value="30" {{ old('payment_terms', $customer->payment_terms) === '30' ? 'selected' : '' }}>30 days</option>
                                    <option value="60" {{ old('payment_terms', $customer->payment_terms) === '60' ? 'selected' : '' }}>60 days</option>
                                    <option value="90" {{ old('payment_terms', $customer->payment_terms) === '90' ? 'selected' : '' }}>90 days</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="mb-8">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b border-gray-200 pb-2">
                            Additional Information
                        </h3>
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                                Notes
                            </label>
                            <textarea id="notes" name="notes" rows="4"
                                      class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                      placeholder="Any additional notes about this customer...">{{ old('notes', $customer->notes) }}</textarea>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex justify-between items-center pt-6 border-t border-gray-200">
                        <div class="flex space-x-3">
                            <button type="submit" 
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-150">
                                💾 Update Customer
                            </button>
                            <a href="{{ route('customers.show', $customer) }}" 
                               class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-2 rounded-lg transition duration-150">
                                Cancel
                            </a>
                        </div>
                        
                        <button type="button" 
                                onclick="if(confirm('Are you sure you want to delete this customer? This action cannot be undone.')) { document.getElementById('delete-form').submit(); }"
                                class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition duration-150">
                            🗑️ Delete Customer
                        </button>
                    </div>
                </form>

                <!-- Hidden Delete Form -->
                <form id="delete-form" action="{{ route('customers.destroy', $customer) }}" method="POST" class="hidden">
                    @csrf
                    @method('DELETE')
                </form>
            </div>

        </div>
    </div>

    <!-- JavaScript for Dynamic Fields -->
    <script>
    function toggleBusinessFields() {
        const type = document.getElementById('type').value;
        const businessFields = document.querySelectorAll('.business-fields');
        const contactPersonSection = document.querySelectorAll('.contact-person-section');
        
        if (type === 'individual') {
            businessFields.forEach(field => field.style.display = 'none');
            contactPersonSection.forEach(field => field.style.display = 'none');
        } else {
            businessFields.forEach(field => field.style.display = 'block');
            contactPersonSection.forEach(field => field.style.display = 'block');
        }
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        toggleBusinessFields();
    });
    </script>
</x-app-layout>