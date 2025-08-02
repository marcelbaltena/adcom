<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <!-- Header -->
            <div class="mb-8">
                <div class="flex justify-between items-start">
                    <div>
                        <div class="flex items-center space-x-3 mb-2">
                            <a href="{{ route('projects.index') }}" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                            </a>
                            <h1 class="text-3xl font-bold text-gray-900">{{ $project->name }}</h1>
                            
                            <!-- Status Badge -->
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($project->status === 'active') bg-green-100 text-green-800
                                @elseif($project->status === 'planning') bg-blue-100 text-blue-800
                                @elseif($project->status === 'on_hold') bg-yellow-100 text-yellow-800
                                @elseif($project->status === 'completed') bg-gray-100 text-gray-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                            </span>
                        </div>
                        
                        @if($project->description)
                            <p class="text-gray-600 max-w-3xl">{{ $project->description }}</p>
                        @endif
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex space-x-3">
                        <a href="{{ route('projects.milestones', $project) }}" 
                           class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded-lg flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Manage Milestones & Budget
                        </a>
                        <a href="{{ route('projects.edit', $project) }}" 
                           class="bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2 px-4 rounded-lg">
                            Edit Project
                        </a>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    
                    <!-- Budget Overview -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-gray-900">Budget Overview</h2>
                            
                            <!-- Budget Status Badge -->
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium
                                @if($project->budget_status === 'under') bg-blue-100 text-blue-800
                                @elseif($project->budget_status === 'on_track') bg-green-100 text-green-800
                                @elseif($project->budget_status === 'warning') bg-yellow-100 text-yellow-800
                                @else bg-red-100 text-red-800 @endif">
                                {{ ucfirst(str_replace('_', ' ', $project->budget_status ?? 'unknown')) }}
                            </span>
                        </div>

                        <!-- Budget Cards -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                            <div class="text-center p-4 bg-gray-50 rounded-lg">
                                <div class="text-2xl font-bold text-gray-900">{{ $project->currency }} {{ number_format($project->budget, 0) }}</div>
                                <div class="text-sm text-gray-600">Total Budget</div>
                            </div>
                            <div class="text-center p-4 bg-blue-50 rounded-lg">
                                <div class="text-2xl font-bold text-blue-600">{{ $project->currency }} {{ number_format($project->allocated_budget, 0) }}</div>
                                <div class="text-sm text-gray-600">Allocated</div>
                            </div>
                            <div class="text-center p-4 bg-orange-50 rounded-lg">
                                <div class="text-2xl font-bold text-orange-600">{{ $project->currency }} {{ number_format($project->spent, 0) }}</div>
                                <div class="text-sm text-gray-600">Spent</div>
                            </div>
                            <div class="text-center p-4 bg-green-50 rounded-lg">
                                <div class="text-2xl font-bold {{ $project->remaining_budget >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ $project->currency }} {{ number_format($project->remaining_budget, 0) }}
                                </div>
                                <div class="text-sm text-gray-600">Remaining</div>
                            </div>
                        </div>

                        <!-- Budget Progress Bar -->
                        <div class="mb-4">
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span class="text-gray-600">Budget Utilization</span>
                                <span class="font-medium">{{ number_format(($project->spent / max($project->budget, 1)) * 100, 1) }}%</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="h-3 rounded-full {{ $project->getBudgetColor() === 'text-green-600' ? 'bg-green-500' : ($project->getBudgetColor() === 'text-yellow-600' ? 'bg-yellow-500' : 'bg-red-500') }}" 
                                     style="width: {{ min(100, ($project->spent / max($project->budget, 1)) * 100) }}%"></div>
                            </div>
                            
                            <!-- Budget Tolerance Info -->
                            <div class="flex items-center justify-between text-xs text-gray-500 mt-2">
                                <span>Tolerance: ±{{ $project->budget_tolerance_percentage }}%</span>
                                <span>Warning at: {{ $project->budget_warning_percentage }}%</span>
                            </div>
                        </div>

                        <!-- Project Value vs Budget -->
                        @if($project->project_value)
                            <div class="border-t pt-4">
                                <div class="flex items-center justify-between text-sm">
                                    <span class="text-gray-600">Project Value (Billing):</span>
                                    <span class="font-medium text-gray-900">{{ $project->currency }} {{ number_format($project->project_value, 2) }}</span>
                                </div>
                                <div class="flex items-center justify-between text-sm mt-1">
                                    <span class="text-gray-600">Profit Margin:</span>
                                    <span class="font-medium {{ ($project->project_value - $project->budget) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $project->currency }} {{ number_format($project->project_value - $project->budget, 2) }}
                                        ({{ number_format((($project->project_value - $project->budget) / max($project->project_value, 1)) * 100, 1) }}%)
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Milestones Overview -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-semibold text-gray-900">Milestones Progress</h2>
                            <a href="{{ route('projects.milestones', $project) }}" 
                               class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                View All →
                            </a>
                        </div>

                        @if($project->milestones->count() > 0)
                            <div class="space-y-4">
                                @foreach($project->milestones->take(3) as $milestone)
                                    <div class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex items-start justify-between mb-3">
                                            <div class="flex-1">
                                                <h3 class="font-medium text-gray-900">{{ $milestone->title }}</h3>
                                                <div class="flex items-center space-x-4 mt-1 text-sm text-gray-600">
                                                    <span>Budget: {{ $project->currency }} {{ number_format($milestone->price, 0) }}</span>
                                                    <span>Spent: {{ $project->currency }} {{ number_format($milestone->spent, 0) }}</span>
                                                    @if($milestone->estimated_hours)
                                                        <span>Hours: {{ $milestone->actual_hours ?? 0 }}/{{ $milestone->estimated_hours }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            <!-- Milestone Status -->
                                            <div class="text-right">
                                                <div class="text-lg font-semibold text-gray-900">{{ $milestone->completion_percentage ?? 0 }}%</div>
                                                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium
                                                    @if($milestone->budget_status === 'under') bg-blue-100 text-blue-800
                                                    @elseif($milestone->budget_status === 'on_track') bg-green-100 text-green-800
                                                    @elseif($milestone->budget_status === 'warning') bg-yellow-100 text-yellow-800
                                                    @else bg-red-100 text-red-800 @endif">
                                                    {{ ucfirst(str_replace('_', ' ', $milestone->budget_status ?? 'unknown')) }}
                                                </span>
                                            </div>
                                        </div>

                                        <!-- Progress Bar -->
                                        <div class="w-full bg-gray-200 rounded-full h-2 mb-2">
                                            <div class="bg-indigo-500 h-2 rounded-full" 
                                                 style="width: {{ min(100, $milestone->completion_percentage ?? 0) }}%"></div>
                                        </div>

                                        <!-- Budget Progress Bar -->
                                        <div class="w-full bg-gray-200 rounded-full h-1">
                                            <div class="bg-{{ $milestone->getBudgetColor() === 'text-green-600' ? 'green' : ($milestone->getBudgetColor() === 'text-yellow-600' ? 'yellow' : 'red') }}-500 h-1 rounded-full" 
                                                 style="width: {{ min(100, ($milestone->spent / max($milestone->price, 1)) * 100) }}%"></div>
                                        </div>
                                    </div>
                                @endforeach

                                @if($project->milestones->count() > 3)
                                    <div class="text-center">
                                        <a href="{{ route('projects.milestones', $project) }}" 
                                           class="text-indigo-600 hover:text-indigo-900 text-sm font-medium">
                                            View {{ $project->milestones->count() - 3 }} more milestones →
                                        </a>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="text-center py-8">
                                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 4h6m-6 4h6m-6 4h6"></path>
                                </svg>
                                <h3 class="mt-2 text-sm font-medium text-gray-900">No milestones yet</h3>
                                <p class="mt-1 text-sm text-gray-500">Get started by creating your first milestone.</p>
                                <div class="mt-6">
                                    <a href="{{ route('projects.milestones', $project) }}" 
                                       class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                        Create Milestone
                                    </a>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Recent Activity -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-xl font-semibold text-gray-900 mb-4">Recent Activity</h2>
                        
                        <!-- This would show recent time entries, budget changes, etc. -->
                        <div class="space-y-3">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0 w-2 h-2 bg-green-500 rounded-full"></div>
                                <div class="flex-1 text-sm text-gray-600">
                                    Project created and budget allocated
                                    <span class="text-gray-400">· {{ $project->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                            
                            @forelse($project->milestones->take(3) as $milestone)
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0 w-2 h-2 bg-blue-500 rounded-full"></div>
                                    <div class="flex-1 text-sm text-gray-600">
                                        Milestone "{{ $milestone->title }}" created
                                        <span class="text-gray-400">· {{ $milestone->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @empty
                                <div class="text-sm text-gray-500 italic">No activity yet</div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    
                    <!-- Project Details -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Project Details</h2>
                        
                        <dl class="space-y-3">
                            <!-- Customer -->
                            @if($project->customer)
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Customer</dt>
                                    <dd class="text-sm text-gray-900">
                                        <a href="{{ route('customers.show', $project->customer) }}" class="hover:text-indigo-600">
                                            {{ $project->customer->name }}
                                        </a>
                                        @if($project->customer->type === 'company')
                                            <div class="text-xs text-gray-500">{{ $project->customer->kvk_number }}</div>
                                        @endif
                                    </dd>
                                </div>
                            @endif

                            <!-- Billing Company -->
                            @if($project->billingCompany)
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Billing Company</dt>
                                    <dd class="text-sm text-gray-900">
                                        <a href="{{ route('companies.show', $project->billingCompany) }}" class="hover:text-indigo-600">
                                            {{ $project->billingCompany->name }}
                                        </a>
                                    </dd>
                                </div>
                            @endif

                            <!-- Created By -->
                            @if($project->createdByCompany && $project->createdByCompany->id !== $project->billingCompany?->id)
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Created By</dt>
                                    <dd class="text-sm text-gray-900">
                                        <a href="{{ route('companies.show', $project->createdByCompany) }}" class="hover:text-indigo-600">
                                            {{ $project->createdByCompany->name }}
                                        </a>
                                    </dd>
                                </div>
                            @endif

                            <!-- Project Manager -->
                            @if($project->user)
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Project Manager</dt>
                                    <dd class="text-sm text-gray-900">{{ $project->user->name }}</dd>
                                </div>
                            @endif

                            <!-- Source -->
                            @if($project->source)
                                <div>
                                    <dt class="text-sm font-medium text-gray-600">Source</dt>
                                    <dd class="text-sm text-gray-900">
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ ucfirst(str_replace('_', ' ', $project->source)) }}
                                        </span>
                                    </dd>
                                </div>
                            @endif

                            <!-- Created Date -->
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Created</dt>
                                <dd class="text-sm text-gray-900">{{ $project->created_at->format('M d, Y') }}</dd>
                            </div>

                            <!-- Last Updated -->
                            <div>
                                <dt class="text-sm font-medium text-gray-600">Last Updated</dt>
                                <dd class="text-sm text-gray-900">{{ $project->updated_at->diffForHumans() }}</dd>
                            </div>
                        </dl>
                    </div>

                    <!-- Quick Stats -->
                    <div class="bg-white rounded-lg shadow-sm p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Stats</h2>
                        
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Milestones</span>
                                <span class="text-sm font-medium text-gray-900">{{ $project->milestones->count() }}</span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Total Tasks</span>
                                <span class="text-sm font-medium text-gray-900">{{ $project->milestones->sum(fn($m) => $m->tasks->count()) }}</span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Completed Milestones</span>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ $project->milestones->where('completion_percentage', 100)->count() }}
                                </span>
                            </div>
                            
                            <div class="flex justify-between items-center">
                                <span class="text-sm text-gray-600">Average Progress</span>
                                <span class="text-sm font-medium text-gray-900">
                                    {{ number_format($project->milestones->avg('completion_percentage') ?? 0, 0) }}%
                                </span>
                            </div>

                            <div class="flex justify-between items-center pt-2 border-t">
                                <span class="text-sm text-gray-600">Budget Efficiency</span>
                                <span class="text-sm font-medium {{ ($project->spent / max($project->budget, 1)) <= 1 ? 'text-green-600' : 'text-red-600' }}">
                                    {{ number_format((1 - ($project->spent / max($project->budget, 1))) * 100, 1) }}%
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Customer Permissions -->
                    @if($project->customer_can_view)
                        <div class="bg-green-50 rounded-lg p-4">
                            <div class="flex items-center">
                                <svg class="w-5 h-5 text-green-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="text-sm font-medium text-green-800">Customer can view this project</span>
                            </div>
                            @if($project->customer_permissions)
                                <div class="mt-2 text-xs text-green-700">
                                    Permissions: {{ implode(', ', json_decode($project->customer_permissions, true) ?? []) }}
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>