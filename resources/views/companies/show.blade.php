{{-- resources/views/companies/show.blade.php --}}
<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $company->name }}</h1>
                        <div class="flex items-center mt-2 space-x-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $company->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $company->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="text-gray-500">{{ $company->users->count() }} users</span>
                            <span class="text-gray-500">{{ $company->billingProjects->count() }} billing projects</span>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('companies.index') }}" 
                           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-150">
                            ← Back to Companies
                        </a>
                        <a href="{{ route('companies.edit', $company) }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-150">
                            ✏️ Edit Company
                        </a>
                    </div>
                </div>
            </div>

            <!-- Company Information Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Company Name</dt>
                            <dd class="text-sm text-gray-900">{{ $company->name }}</dd>
                        </div>
                        @if($company->description)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="text-sm text-gray-900">{{ $company->description }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Default Hourly Rate</dt>
                            <dd class="text-sm text-gray-900">€{{ number_format($company->default_hourly_rate, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Currency</dt>
                            <dd class="text-sm text-gray-900">{{ $company->currency }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Timezone</dt>
                            <dd class="text-sm text-gray-900">{{ $company->timezone }}</dd>
                        </div>
                    </dl>
                </div>

                <!-- Contact Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                    <dl class="space-y-3">
                        @if($company->email)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="text-sm text-gray-900">
                                <a href="mailto:{{ $company->email }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $company->email }}
                                </a>
                            </dd>
                        </div>
                        @endif
                        @if($company->phone)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="text-sm text-gray-900">
                                <a href="tel:{{ $company->phone }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $company->phone }}
                                </a>
                            </dd>
                        </div>
                        @endif
                        @if($company->website)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Website</dt>
                            <dd class="text-sm text-gray-900">
                                <a href="{{ $company->website }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                    {{ $company->website }} ↗
                                </a>
                            </dd>
                        </div>
                        @endif
                        @if($company->full_address)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                            <dd class="text-sm text-gray-900 whitespace-pre-line">{{ $company->full_address }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Business Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Business Information</h3>
                    <dl class="space-y-3">
                        @if($company->kvk_number)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">KvK Number</dt>
                            <dd class="text-sm text-gray-900">{{ $company->kvk_number }}</dd>
                        </div>
                        @endif
                        @if($company->vat_number)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">VAT Number</dt>
                            <dd class="text-sm text-gray-900">{{ $company->vat_number }}</dd>
                        </div>
                        @endif
                        @if($company->iban)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">IBAN</dt>
                            <dd class="text-sm text-gray-900 font-mono">{{ $company->iban }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created</dt>
                            <dd class="text-sm text-gray-900">{{ $company->created_at->format('d M Y') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Users Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Company Users ({{ $company->users->count() }})</h3>
                        <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-150 text-sm">
                            ➕ Add User
                        </button>
                    </div>
                </div>
                <div class="p-6">
                    @if($company->users->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($company->users as $user)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex items-center space-x-3">
                                        <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" 
                                             class="w-10 h-10 rounded-full">
                                        <div class="flex-1">
                                            <h4 class="text-sm font-medium text-gray-900">{{ $user->name }}</h4>
                                            <p class="text-xs text-gray-500">{{ $user->email }}</p>
                                            <div class="flex items-center space-x-2 mt-1">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                    {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-800' : 
                                                       ($user->role === 'manager' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                                    {{ ucfirst($user->role) }}
                                                </span>
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                    {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ $user->is_active ? 'Active' : 'Inactive' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    @if($user->hourly_rate)
                                        <div class="mt-2 text-xs text-gray-600">
                                            Rate: €{{ number_format($user->hourly_rate, 2) }}/hour
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-400 text-6xl mb-4">👥</div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Users Yet</h3>
                            <p class="text-gray-600 mb-4">This company doesn't have any users assigned yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Projects Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Company Projects</h3>
                </div>
                <div class="p-6">
                    @if($company->billingProjects->count() > 0 || $company->createdProjects->count() > 0)
                        
                        <!-- Billing Projects -->
                        @if($company->billingProjects->count() > 0)
                            <div class="mb-6">
                                <h4 class="text-md font-medium text-gray-900 mb-3">Billing Projects ({{ $company->billingProjects->count() }})</h4>
                                <div class="space-y-3">
                                    @foreach($company->billingProjects as $project)
                                        <div class="border border-gray-200 rounded-lg p-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h5 class="font-medium text-gray-900">
                                                        <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:text-blue-800">
                                                            {{ $project->name }}
                                                        </a>
                                                    </h5>
                                                    @if($project->description)
                                                        <p class="text-sm text-gray-600 mt-1">{{ Str::limit($project->description, 100) }}</p>
                                                    @endif
                                                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                                        <span>Status: {{ ucfirst($project->status) }}</span>
                                                        @if($project->project_value)
                                                            <span>Value: {{ $project->currency }} {{ number_format($project->project_value, 2) }}</span>
                                                        @endif
                                                        <span>Progress: {{ $project->progress }}%</span>
                                                    </div>
                                                </div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    Billing Owner
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Created Projects (if different from billing) -->
                        @php
                            $createdOnly = $company->createdProjects->reject(function($project) use ($company) {
                                return $project->billing_company_id === $company->id;
                            });
                        @endphp
                        
                        @if($createdOnly->count() > 0)
                            <div>
                                <h4 class="text-md font-medium text-gray-900 mb-3">Created Projects ({{ $createdOnly->count() }})</h4>
                                <div class="space-y-3">
                                    @foreach($createdOnly as $project)
                                        <div class="border border-gray-200 rounded-lg p-4">
                                            <div class="flex justify-between items-start">
                                                <div>
                                                    <h5 class="font-medium text-gray-900">
                                                        <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:text-blue-800">
                                                            {{ $project->name }}
                                                        </a>
                                                    </h5>
                                                    @if($project->description)
                                                        <p class="text-sm text-gray-600 mt-1">{{ Str::limit($project->description, 100) }}</p>
                                                    @endif
                                                    <div class="flex items-center space-x-4 mt-2 text-xs text-gray-500">
                                                        <span>Status: {{ ucfirst($project->status) }}</span>
                                                        @if($project->billingCompany)
                                                            <span>Billed by: {{ $project->billingCompany->name }}</span>
                                                        @endif
                                                        <span>Progress: {{ $project->progress }}%</span>
                                                    </div>
                                                </div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    Creator
                                                </span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                        
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-400 text-6xl mb-4">📂</div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Projects Yet</h3>
                            <p class="text-gray-600 mb-4">This company doesn't have any projects yet.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>