<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Projects</h1>
                        <p class="text-gray-600 mt-2">Manage your project portfolio with budget tracking</p>
                    </div>
                    <a href="{{ route('projects.create') }}" 
                       class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                        </svg>
                        New Project
                    </a>
                </div>
            </div>

            <!-- Filters -->
            <div class="bg-white rounded-lg shadow-sm p-6 mb-8">
                <form method="GET" action="{{ route('projects.index') }}" class="flex flex-wrap items-center gap-4">
                    <!-- Search -->
                    <div class="flex-1 min-w-64">
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search projects..." 
                               class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                    </div>

                    <!-- Customer Filter -->
                    <div class="min-w-48">
                        <select name="customer_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Customers</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ request('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                    @if($customer->type === 'company') ({{ $customer->kvk_number }}) @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Company Filter -->
                    <div class="min-w-48">
                        <select name="company_id" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Companies</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Status Filter -->
                    <div class="min-w-32">
                        <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">All Status</option>
                            <option value="planning" {{ request('status') === 'planning' ? 'selected' : '' }}>Planning</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="on_hold" {{ request('status') === 'on_hold' ? 'selected' : '' }}>On Hold</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <!-- Budget Status Filter -->
                    <div class="min-w-32">
                        <select name="budget_status" class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                            <option value="">Budget Status</option>
                            <option value="under" {{ request('budget_status') === 'under' ? 'selected' : '' }}>Under Budget</option>
                            <option value="on_track" {{ request('budget_status') === 'on_track' ? 'selected' : '' }}>On Track</option>
                            <option value="warning" {{ request('budget_status') === 'warning' ? 'selected' : '' }}>Warning</option>
                            <option value="over" {{ request('budget_status') === 'over' ? 'selected' : '' }}>Over Budget</option>
                        </select>
                    </div>

                    <!-- Filter Buttons -->
                    <div class="flex space-x-2">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                            Filter
                        </button>
                        <a href="{{ route('projects.index') }}" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-md text-sm font-medium">
                            Clear
                        </a>
                    </div>
                </form>
            </div>

            <!-- Projects Grid -->
            @if($projects->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($projects as $project)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-lg transition-shadow duration-200">
                            <!-- Project Header -->
                            <div class="p-6">
                                <div class="flex items-start justify-between mb-4">
                                    <div class="flex-1">
                                        <h3 class="text-lg font-semibold text-gray-900 mb-1">
                                            <a href="{{ route('projects.show', $project) }}" class="hover:text-indigo-600">
                                                {{ $project->name }}
                                            </a>
                                        </h3>
                                        
                                        <!-- Customer Info -->
                                        @if($project->customer)
                                            <p class="text-sm text-gray-600 mb-2">
                                                <span class="font-medium">Customer:</span> {{ $project->customer->name }}
                                                @if($project->customer->type === 'company') 
                                                    <span class="text-gray-400">({{ $project->customer->kvk_number }})</span>
                                                @endif
                                            </p>
                                        @endif

                                        <!-- Company Info -->
                                        <div class="text-xs text-gray-500 space-y-1">
                                            @if($project->billingCompany)
                                                <div>Billing: {{ $project->billingCompany->name }}</div>
                                            @endif
                                            @if($project->createdByCompany && $project->createdByCompany->id !== $project->billingCompany?->id)
                                                <div>Created by: {{ $project->createdByCompany->name }}</div>
                                            @endif
                                        </div>
                                    </div>

                                    <!-- Status Badge -->
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                        @if($project->status === 'active') bg-green-100 text-green-800
                                        @elseif($project->status === 'planning') bg-blue-100 text-blue-800
                                        @elseif($project->status === 'on_hold') bg-yellow-100 text-yellow-800
                                        @elseif($project->status === 'completed') bg-gray-100 text-gray-800
                                        @else bg-red-100 text-red-800 @endif">
                                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                    </span>
                                </div>

                                <!-- Budget Overview -->
                                <div class="mb-4">
                                    <div class="flex items-center justify-between text-sm mb-2">
                                        <span class="text-gray-600">Budget Progress</span>
                                        <div class="text-right">
                                            <div class="font-medium {{ $project->getBudgetColor() }}">
                                                {{ $project->currency }} {{ number_format($project->spent, 0) }} / {{ number_format($project->budget, 0) }}
                                            </div>
                                            <div class="text-xs text-gray-500">
                                                {{ number_format(($project->spent / max($project->budget, 1)) * 100, 1) }}%
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Budget Progress Bar -->
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="h-2 rounded-full {{ $project->getBudgetColor() === 'text-green-600' ? 'bg-green-500' : ($project->getBudgetColor() === 'text-yellow-600' ? 'bg-yellow-500' : 'bg-red-500') }}" 
                                             style="width: {{ min(100, ($project->spent / max($project->budget, 1)) * 100) }}%"></div>
                                    </div>

                                    <!-- Budget Status Badge -->
                                    <div class="mt-2 flex items-center justify-between">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                            @if($project->budget_status === 'under') bg-blue-100 text-blue-800
                                            @elseif($project->budget_status === 'on_track') bg-green-100 text-green-800
                                            @elseif($project->budget_status === 'warning') bg-yellow-100 text-yellow-800
                                            @else bg-red-100 text-red-800 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $project->budget_status ?? 'unknown')) }}
                                        </span>
                                        
                                        <div class="text-xs text-gray-500">
                                            Remaining: {{ $project->currency }} {{ number_format($project->remaining_budget, 0) }}
                                        </div>
                                    </div>
                                </div>

                                <!-- Project Stats -->
                                <div class="grid grid-cols-3 gap-3 text-center text-sm border-t pt-4">
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $project->milestones->count() }}</div>
                                        <div class="text-gray-500 text-xs">Milestones</div>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">{{ $project->milestones->sum(fn($m) => $m->tasks->count()) }}</div>
                                        <div class="text-gray-500 text-xs">Tasks</div>
                                    </div>
                                    <div>
                                        <div class="font-medium text-gray-900">
                                            {{ number_format($project->milestones->avg('completion_percentage') ?? 0, 0) }}%
                                        </div>
                                        <div class="text-gray-500 text-xs">Complete</div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex space-x-2 mt-4 pt-4 border-t">
                                    <a href="{{ route('projects.show', $project) }}" 
                                       class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium">
                                        View Details
                                    </a>
                                    <a href="{{ route('projects.milestones', $project) }}" 
                                       class="flex-1 bg-green-600 hover:bg-green-700 text-white text-center py-2 px-3 rounded-md text-sm font-medium">
                                        📊 Milestones
                                    </a>
                                    <a href="{{ route('projects.edit', $project) }}" 
                                       class="bg-gray-300 hover:bg-gray-400 text-gray-700 py-2 px-3 rounded-md text-sm font-medium">
                                        Edit
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pagination -->
                <div class="mt-8">
                    {{ $projects->appends(request()->query())->links() }}
                </div>
            @else
                <!-- Empty State -->
                <div class="bg-white rounded-lg shadow-sm p-12">
                    <div class="text-center">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        <h3 class="mt-2 text-sm font-medium text-gray-900">No projects found</h3>
                        <p class="mt-1 text-sm text-gray-500">
                            @if(request()->hasAny(['search', 'customer_id', 'company_id', 'status', 'budget_status']))
                                No projects match your current filters.
                            @else
                                Get started by creating your first project.
                            @endif
                        </p>
                        <div class="mt-6">
                            @if(request()->hasAny(['search', 'customer_id', 'company_id', 'status', 'budget_status']))
                                <a href="{{ route('projects.index') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                    Clear Filters
                                </a>
                            @else
                                <a href="{{ route('projects.create') }}" 
                                   class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                                    </svg>
                                    New Project
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

        </div>
    </div>
</x-app-layout>
