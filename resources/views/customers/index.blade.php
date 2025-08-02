<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Customers</h1>
                        <p class="text-gray-600 mt-2">Manage your customer base</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('customers.create') }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-150">
                            ➕ Add Customer
                        </a>
                    </div>
                </div>
            </div>

            <!-- Filters & Search -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
                <form method="GET" action="{{ route('customers.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                            <input type="text" id="search" name="search" 
                                   value="{{ request('search') }}"
                                   placeholder="Name, email, contact..."
                                   class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                        </div>

                        <!-- Company Filter -->
                        <div>
                            <label for="company_id" class="block text-sm font-medium text-gray-700 mb-1">Company</label>
                            <select id="company_id" name="company_id" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Companies</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Type Filter -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                            <select id="type" name="type" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Types</option>
                                <option value="company" {{ request('type') === 'company' ? 'selected' : '' }}>Company</option>
                                <option value="individual" {{ request('type') === 'individual' ? 'selected' : '' }}>Individual</option>
                            </select>
                        </div>

                        <!-- Status Filter -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select id="status" name="status" 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">All Status</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="flex justify-between items-center">
                        <button type="submit" 
                                class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-150">
                            🔍 Filter
                        </button>
                        <a href="{{ route('customers.index') }}" 
                           class="text-gray-600 hover:text-gray-800 text-sm">
                            Clear Filters
                        </a>
                    </div>
                </form>
            </div>

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

            <!-- Customers Grid -->
            @if($customers->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                    @foreach($customers as $customer)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition duration-150">
                            <!-- Customer Header -->
                            <div class="p-6 border-b border-gray-200">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900">
                                            <a href="{{ route('customers.show', $customer) }}" class="text-blue-600 hover:text-blue-800">
                                                {{ $customer->name }}
                                            </a>
                                        </h3>
                                        <p class="text-sm text-gray-600 mt-1">{{ $customer->company->name }}</p>
                                        @if($customer->primary_contact)
                                            <p class="text-xs text-gray-500 mt-1">{{ $customer->primary_contact }}</p>
                                        @endif
                                    </div>
                                    <div class="flex items-center space-x-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $customer->type === 'company' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                            {{ $customer->type === 'company' ? '🏢 Company' : '👤 Individual' }}
                                        </span>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                            {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $customer->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Customer Info -->
                            <div class="p-6">
                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="text-gray-500">Projects:</span>
                                        <span class="font-medium">{{ $customer->projects_count }}</span>
                                    </div>
                                    <div>
                                        <span class="text-gray-500">Currency:</span>
                                        <span class="font-medium">{{ $customer->currency }}</span>
                                    </div>
                                    @if($customer->city)
                                    <div>
                                        <span class="text-gray-500">Location:</span>
                                        <span class="font-medium">{{ $customer->city }}</span>
                                    </div>
                                    @endif
                                    @if($customer->industry)
                                    <div>
                                        <span class="text-gray-500">Industry:</span>
                                        <span class="font-medium">{{ ucfirst($customer->industry) }}</span>
                                    </div>
                                    @endif
                                </div>

                                @if($customer->description)
                                    <p class="text-sm text-gray-600 mt-4">{{ Str::limit($customer->description, 100) }}</p>
                                @endif
                            </div>

                            <!-- Actions -->
                            <div class="px-6 py-3 bg-gray-50 border-t border-gray-200">
                                <div class="flex justify-between items-center">
                                    <div class="flex space-x-2">
                                        <a href="{{ route('customers.show', $customer) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            View
                                        </a>
                                        <a href="{{ route('customers.edit', $customer) }}" 
                                           class="text-green-600 hover:text-green-800 text-sm font-medium">
                                            Edit
                                        </a>
                                        <a href="{{ route('customers.create-project', $customer) }}" 
                                           class="text-purple-600 hover:text-purple-800 text-sm font-medium">
                                            + Project
                                        </a>
                                    </div>
                                    <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="inline"
                                          onsubmit="return confirm('Are you sure you want to delete this customer?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-medium">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $customers->appends(request()->query())->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="text-gray-400 text-6xl mb-4">👥</div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No customers found</h3>
                    <p class="text-gray-600 mb-6">
                        @if(request()->hasAny(['search', 'company_id', 'type', 'status']))
                            No customers match your current filters. Try adjusting your search criteria.
                        @else
                            Get started by adding your first customer to the system.
                        @endif
                    </p>
                    <a href="{{ route('customers.create') }}" 
                       class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg transition duration-150">
                        ➕ Add First Customer
                    </a>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>