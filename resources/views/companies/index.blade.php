{{-- resources/views/companies/index.blade.php --}}
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">🏢 Companies</h1>
                        <p class="text-gray-600 mt-1">Manage companies in the system</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('companies.create') }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-colors">
                            ➕ Add Company
                        </a>
                    </div>
                </div>
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

            <!-- Companies List -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    @if($companies->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                            @foreach($companies as $company)
                                <div class="bg-gray-50 rounded-lg p-6 border border-gray-200 hover:shadow-md transition-shadow">
                                    <!-- Company Header -->
                                    <div class="flex justify-between items-start mb-4">
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-900">
                                                {{ $company->name }}
                                            </h3>
                                            @if($company->legal_name && $company->legal_name !== $company->name)
                                                <p class="text-sm text-gray-500">{{ $company->legal_name }}</p>
                                            @endif
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <!-- Status Badge -->
                                            <span class="px-2 py-1 text-xs rounded-full {{ $company->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                {{ $company->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </div>
                                    </div>

                                    <!-- Company Info -->
                                    <div class="space-y-2 mb-4">
                                        @if($company->email)
                                            <div class="flex items-center text-sm text-gray-600">
                                                <span class="mr-2">📧</span>
                                                <span>{{ $company->email }}</span>
                                            </div>
                                        @endif
                                        
                                        @if($company->phone)
                                            <div class="flex items-center text-sm text-gray-600">
                                                <span class="mr-2">📞</span>
                                                <span>{{ $company->phone }}</span>
                                            </div>
                                        @endif

                                        @if($company->city)
                                            <div class="flex items-center text-sm text-gray-600">
                                                <span class="mr-2">📍</span>
                                                <span>{{ $company->city }}, {{ $company->country }}</span>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Company Stats -->
                                    <div class="bg-white rounded p-3 mb-4">
                                        <div class="grid grid-cols-2 gap-4 text-center">
                                            <div>
                                                <div class="text-2xl font-bold text-blue-600">
                                                    {{ $company->active_users_count }}
                                                </div>
                                                <div class="text-xs text-gray-500">Active Users</div>
                                            </div>
                                            <div>
                                                <div class="text-2xl font-bold text-green-600">
                                                    €{{ number_format($company->default_hourly_rate ?? 0, 0) }}
                                                </div>
                                                <div class="text-xs text-gray-500">Default Rate</div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex justify-between items-center">
                                        <a href="{{ route('companies.show', $company) }}" 
                                           class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                            View Details →
                                        </a>
                                        <div class="flex space-x-2">
                                            <a href="{{ route('companies.edit', $company) }}" 
                                               class="text-gray-400 hover:text-blue-600 transition-colors" 
                                               title="Edit">
                                                ✏️
                                            </a>
                                            @if($company->active_users_count == 0)
                                                <form action="{{ route('companies.destroy', $company) }}" 
                                                      method="POST" 
                                                      class="inline"
                                                      onsubmit="return confirm('Are you sure you want to delete this company?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" 
                                                            class="text-gray-400 hover:text-red-600 transition-colors" 
                                                            title="Delete">
                                                        🗑️
                                                    </button>
                                                </form>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Pagination -->
                        @if($companies->hasPages())
                            <div class="mt-6">
                                {{ $companies->links() }}
                            </div>
                        @endif
                    @else
                        <!-- Empty State -->
                        <div class="text-center py-12">
                            <div class="text-gray-400 mb-4">
                                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No companies yet</h3>
                            <p class="text-gray-600 mb-6">Get started by creating your first company.</p>
                            <a href="{{ route('companies.create') }}" 
                               class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg transition-colors">
                                ➕ Create First Company
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>