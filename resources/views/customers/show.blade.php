<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">{{ $customer->name }}</h1>
                        <div class="flex items-center mt-2 space-x-4">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $customer->type === 'company' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800' }}">
                                {{ $customer->type === 'company' ? '🏢 Company' : '👤 Individual' }}
                            </span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                {{ $customer->is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                {{ $customer->is_active ? 'Active' : 'Inactive' }}
                            </span>
                            <span class="text-gray-500">{{ $customer->company->name }}</span>
                            <span class="text-gray-500">{{ $customer->projects->count() }} projects</span>
                        </div>
                    </div>
                    <div class="flex space-x-3">
                        <a href="{{ route('customers.index') }}" 
                           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg transition duration-150">
                            ← Back to Customers
                        </a>
                        <a href="{{ route('customers.edit', $customer) }}" 
                           class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition duration-150">
                            ✏️ Edit Customer
                        </a>
                        <a href="{{ route('customers.create-project', $customer) }}" 
                           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-150">
                            ➕ New Project
                        </a>
                    </div>
                </div>
            </div>

            <!-- Customer Information Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Customer Name</dt>
                            <dd class="text-sm text-gray-900">{{ $customer->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Type</dt>
                            <dd class="text-sm text-gray-900">{{ ucfirst($customer->type) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Company</dt>
                            <dd class="text-sm text-gray-900">
                                <a href="{{ route('companies.show', $customer->company) }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $customer->company->name }}
                                </a>
                            </dd>
                        </div>
                        @if($customer->description)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="text-sm text-gray-900">{{ $customer->description }}</dd>
                        </div>
                        @endif
                        @if($customer->industry)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Industry</dt>
                            <dd class="text-sm text-gray-900">{{ ucfirst($customer->industry) }}</dd>
                        </div>
                        @endif
                        @if($customer->size && $customer->type === 'company')
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Company Size</dt>
                            <dd class="text-sm text-gray-900">{{ ucfirst($customer->size) }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Contact Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact Information</h3>
                    <dl class="space-y-3">
                        @if($customer->email)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Email</dt>
                            <dd class="text-sm text-gray-900">
                                <a href="mailto:{{ $customer->email }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $customer->email }}
                                </a>
                            </dd>
                        </div>
                        @endif
                        @if($customer->phone)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Phone</dt>
                            <dd class="text-sm text-gray-900">
                                <a href="tel:{{ $customer->phone }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $customer->phone }}
                                </a>
                            </dd>
                        </div>
                        @endif
                        @if($customer->website)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Website</dt>
                            <dd class="text-sm text-gray-900">
                                <a href="{{ $customer->website }}" target="_blank" class="text-blue-600 hover:text-blue-800">
                                    {{ $customer->website }} ↗
                                </a>
                            </dd>
                        </div>
                        @endif
                        @if($customer->contact_person)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Contact Person</dt>
                            <dd class="text-sm text-gray-900">{{ $customer->contact_person }}</dd>
                        </div>
                        @endif
                        @if($customer->contact_email)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Contact Email</dt>
                            <dd class="text-sm text-gray-900">
                                <a href="mailto:{{ $customer->contact_email }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $customer->contact_email }}
                                </a>
                            </dd>
                        </div>
                        @endif
                        @if($customer->contact_phone)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Contact Phone</dt>
                            <dd class="text-sm text-gray-900">
                                <a href="tel:{{ $customer->contact_phone }}" class="text-blue-600 hover:text-blue-800">
                                    {{ $customer->contact_phone }}
                                </a>
                            </dd>
                        </div>
                        @endif
                        @if($customer->full_address)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Address</dt>
                            <dd class="text-sm text-gray-900 whitespace-pre-line">{{ $customer->full_address }}</dd>
                        </div>
                        @endif
                    </dl>
                </div>

                <!-- Business & Financial Information -->
                <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">
                        {{ $customer->type === 'company' ? 'Business Information' : 'Financial Information' }}
                    </h3>
                    <dl class="space-y-3">
                        @if($customer->kvk_number && $customer->type === 'company')
                        <div>
                            <dt class="text-sm font-medium text-gray-500">KvK Number</dt>
                            <dd class="text-sm text-gray-900">{{ $customer->kvk_number }}</dd>
                        </div>
                        @endif
                        @if($customer->vat_number && $customer->type === 'company')
                        <div>
                            <dt class="text-sm font-medium text-gray-500">VAT Number</dt>
                            <dd class="text-sm text-gray-900">{{ $customer->vat_number }}</dd>
                        </div>
                        @endif
                        @if($customer->iban)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">IBAN</dt>
                            <dd class="text-sm text-gray-900 font-mono">{{ $customer->iban }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Currency</dt>
                            <dd class="text-sm text-gray-900">{{ $customer->currency }}</dd>
                        </div>
                        @if($customer->default_hourly_rate)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Default Hourly Rate</dt>
                            <dd class="text-sm text-gray-900">{{ $customer->currency }} {{ number_format($customer->default_hourly_rate, 2) }}</dd>
                        </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Payment Terms</dt>
                            <dd class="text-sm text-gray-900">{{ $customer->payment_terms }} days</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Customer Since</dt>
                            <dd class="text-sm text-gray-900">{{ $customer->created_at->format('d M Y') }}</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <!-- Projects Section -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 mb-8">
                <div class="p-6 border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <h3 class="text-lg font-semibold text-gray-900">Projects ({{ $customer->projects->count() }})</h3>
                        <a href="{{ route('customers.create-project', $customer) }}" 
                           class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition duration-150 text-sm">
                            ➕ New Project
                        </a>
                    </div>
                </div>
                <div class="p-6">
                    @if($customer->projects->count() > 0)
                        <div class="space-y-4">
                            @foreach($customer->projects as $project)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex justify-between items-start">
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900">
                                                <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:text-blue-800">
                                                    {{ $project->name }}
                                                </a>
                                            </h4>
                                            @if($project->description)
                                                <p class="text-sm text-gray-600 mt-1">{{ Str::limit($project->description, 150) }}</p>
                                            @endif
                                            <div class="flex items-center space-x-4 mt-3 text-xs text-gray-500">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium
                                                    {{ $project->status === 'completed' ? 'bg-green-100 text-green-800' : 
                                                       ($project->status === 'active' ? 'bg-blue-100 text-blue-800' : 'bg-gray-100 text-gray-800') }}">
                                                    {{ ucfirst($project->status) }}
                                                </span>
                                                @if($project->project_value)
                                                    <span>Value: {{ $project->currency }} {{ number_format($project->project_value, 2) }}</span>
                                                @endif
                                                <span>Progress: {{ $project->progress }}%</span>
                                                <span>{{ $project->milestones->count() }} milestones</span>
                                                <span>Created: {{ $project->created_at->format('d M Y') }}</span>
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <!-- Progress Bar -->
                                            <div class="w-24 bg-gray-200 rounded-full h-2">
                                                <div class="bg-blue-600 h-2 rounded-full" style="width: {{ $project->progress }}%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500 mt-1 block text-center">{{ $project->progress }}%</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <!-- Project Statistics -->
                        <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4 p-4 bg-gray-50 rounded-lg">
                            <div class="text-center">
                                <div class="text-2xl font-bold text-gray-900">{{ $customer->projects->count() }}</div>
                                <div class="text-sm text-gray-600">Total Projects</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-green-600">{{ $customer->getActiveProjectsCount() }}</div>
                                <div class="text-sm text-gray-600">Active Projects</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-blue-600">{{ $customer->getCompletedProjectsCount() }}</div>
                                <div class="text-sm text-gray-600">Completed Projects</div>
                            </div>
                            <div class="text-center">
                                <div class="text-2xl font-bold text-purple-600">{{ $customer->currency }} {{ number_format($customer->getTotalProjectValue(), 0) }}</div>
                                <div class="text-sm text-gray-600">Total Value</div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <div class="text-gray-400 text-6xl mb-4">📂</div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">No Projects Yet</h3>
                            <p class="text-gray-600 mb-4">This customer doesn't have any projects yet.</p>
                            <a href="{{ route('customers.create-project', $customer) }}" 
                               class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg transition duration-150">
                                ➕ Create First Project
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Notes Section -->
            @if($customer->notes)
            <div class="bg-white rounded-lg shadow-sm border border-gray-200">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Notes</h3>
                </div>
                <div class="p-6">
                    <p class="text-gray-700 whitespace-pre-line">{{ $customer->notes }}</p>
                </div>
            </div>
            @endif

        </div>
    </div>
</x-app-layout>