{{-- resources/views/livewire/project-dashboard.blade.php --}}

<div class="space-y-6">
    <!-- Dashboard Header with Filters -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center space-y-4 lg:space-y-0">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Project Dashboard</h1>
                <p class="text-gray-600 mt-1">Overview of all your projects, milestones, and team performance</p>
            </div>
            
            <!-- Filter Controls -->
            <div class="flex flex-wrap items-center space-x-4 space-y-2 lg:space-y-0">
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Period:</label>
                    <select wire:model.live="selectedPeriod" class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="7">Last 7 days</option>
                        <option value="30">Last 30 days</option>
                        <option value="90">Last 90 days</option>
                    </select>
                </div>
                
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Project:</label>
                    <select wire:model.live="selectedProject" class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all">All Projects</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}">{{ $project->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div class="flex items-center space-x-2">
                    <label class="text-sm font-medium text-gray-700">Team Member:</label>
                    <select wire:model.live="selectedTeamMember" class="text-sm border-gray-300 rounded-md focus:ring-indigo-500 focus:border-indigo-500">
                        <option value="all">All Members</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <button wire:click="loadDashboardData" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium flex items-center space-x-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    <span>Refresh</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Summary Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Total Projects Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-indigo-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Total Projects</h3>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($totalProjects) }}</p>
                </div>
            </div>
        </div>

        <!-- Completion Rate Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-green-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Completion Rate</h3>
                    <p class="text-2xl font-semibold text-gray-900">{{ $completionRate }}%</p>
                    <div class="mt-2 w-full bg-gray-200 rounded-full h-2">
                        <div class="bg-green-500 h-2 rounded-full" style="width: {{ $completionRate }}%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Budget Overview Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Budget</h3>
                    <p class="text-2xl font-semibold text-gray-900">€{{ number_format($totalBudget, 0) }}</p>
                    <p class="text-sm text-gray-500">€{{ number_format($spentBudget, 0) }} spent</p>
                </div>
            </div>
        </div>

        <!-- Team Members Card -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <div class="w-8 h-8 bg-purple-500 rounded-lg flex items-center justify-center">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z"></path>
                        </svg>
                    </div>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-gray-500">Team Members</h3>
                    <p class="text-2xl font-semibold text-gray-900">{{ number_format($teamMembers) }}</p>
                    @if($overdueItems > 0)
                        <p class="text-sm text-red-500">{{ $overdueItems }} overdue items</p>
                    @else
                        <p class="text-sm text-green-500">All on track</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Progress Chart -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Progress Over Time</h3>
                <div class="flex items-center space-x-2">
                    <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                    <span class="text-sm text-gray-600">Completion Rate</span>
                </div>
            </div>
            <div class="h-64">
                <canvas id="progressChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Budget Chart -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Budget by Status</h3>
                <div class="text-sm text-gray-500">
                    Total: €{{ number_format($totalBudget, 0) }}
                </div>
            </div>
            <div class="h-64 flex items-center justify-center">
                <canvas id="budgetChart" width="300" height="300"></canvas>
            </div>
        </div>
    </div>

    <!-- Team Productivity & Activity Timeline -->
    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <!-- Team Productivity Chart -->
        <div class="xl:col-span-2 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-medium text-gray-900">Team Productivity</h3>
                <div class="flex items-center space-x-4">
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-blue-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Milestones</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <div class="w-3 h-3 bg-green-500 rounded-full"></div>
                        <span class="text-sm text-gray-600">Tasks</span>
                    </div>
                </div>
            </div>
            <div class="h-64">
                <canvas id="teamProductivityChart" width="400" height="200"></canvas>
            </div>
        </div>

        <!-- Top Performers -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Top Performers</h3>
            <div class="space-y-4">
                @forelse($topPerformers as $index => $performer)
                    <div class="flex items-center space-x-3">
                        <div class="flex-shrink-0">
                            <div class="w-8 h-8 rounded-full bg-gradient-to-r from-indigo-500 to-purple-600 flex items-center justify-center text-white text-sm font-medium">
                                {{ $index + 1 }}
                            </div>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ $performer->name }}</p>
                            <p class="text-xs text-gray-500">{{ $performer->completed_items }} completed items</p>
                        </div>
                        <div class="flex-shrink-0">
                            <div class="flex items-center space-x-1">
                                <div class="w-2 h-2 bg-blue-500 rounded-full" title="Milestones"></div>
                                <span class="text-xs text-gray-600">{{ $performer->completed_milestones }}</span>
                                <div class="w-2 h-2 bg-green-500 rounded-full ml-2" title="Tasks"></div>
                                <span class="text-xs text-gray-600">{{ $performer->completed_tasks }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500">No performance data available</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Activity Timeline & Upcoming Deadlines -->
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
        <!-- Activity Timeline -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Activity Timeline</h3>
            <div class="h-48 mb-4">
                <canvas id="activityChart" width="400" height="150"></canvas>
            </div>
            
            <!-- Recent Activities List -->
            <div class="border-t border-gray-200 pt-4">
                <h4 class="text-sm font-medium text-gray-900 mb-3">Recent Activities</h4>
                <div class="space-y-3 max-h-48 overflow-y-auto">
                    @forelse($recentActivities as $activity)
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <div class="w-6 h-6 bg-gray-100 rounded-full flex items-center justify-center">
                                    <div class="w-2 h-2 bg-purple-500 rounded-full"></div>
                                </div>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm text-gray-900">
                                    <span class="font-medium">{{ $activity->user_name }}</span>
                                    {{ $activity->description }}
                                </p>
                                <p class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($activity->created_at)->diffForHumans() }}</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">No recent activities</p>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Upcoming Deadlines -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Upcoming Deadlines</h3>
            <div class="space-y-4">
                @forelse($upcomingDeadlines as $deadline)
                    @php
                        $daysUntilDue = \Carbon\Carbon::parse($deadline->due_date)->diffInDays(now());
                        $isUrgent = $daysUntilDue <= 3;
                        $isOverdue = \Carbon\Carbon::parse($deadline->due_date)->isPast();
                    @endphp
                    <div class="flex items-center space-x-4 p-3 rounded-lg {{ $isOverdue ? 'bg-red-50 border border-red-200' : ($isUrgent ? 'bg-yellow-50 border border-yellow-200' : 'bg-gray-50') }}">
                        <div class="flex-shrink-0">
                            @if($isOverdue)
                                <div class="w-8 h-8 bg-red-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            @elseif($isUrgent)
                                <div class="w-8 h-8 bg-yellow-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                </div>
                            @else
                                <div class="w-8 h-8 bg-blue-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                                    </svg>
                                </div>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium {{ $isOverdue ? 'text-red-900' : ($isUrgent ? 'text-yellow-900' : 'text-gray-900') }}">
                                {{ $deadline->title }}
                            </p>
                            <p class="text-xs text-gray-600">{{ $deadline->project->name ?? 'Unknown Project' }}</p>
                            <p class="text-xs {{ $isOverdue ? 'text-red-600' : ($isUrgent ? 'text-yellow-600' : 'text-gray-500') }}">
                                @if($isOverdue)
                                    Overdue by {{ \Carbon\Carbon::parse($deadline->due_date)->diffForHumans() }}
                                @else
                                    Due {{ \Carbon\Carbon::parse($deadline->due_date)->diffForHumans() }}
                                @endif
                            </p>
                        </div>
                        @if($deadline->assignees && $deadline->assignees->count() > 0)
                            <div class="flex-shrink-0">
                                <div class="flex -space-x-1">
                                    @foreach($deadline->assignees->take(3) as $assignee)
                                        <div class="w-6 h-6 rounded-full bg-gray-300 border-2 border-white flex items-center justify-center">
                                            <span class="text-xs font-medium text-gray-700">{{ substr($assignee->user->name ?? 'U', 0, 1) }}</span>
                                        </div>
                                    @endforeach
                                    @if($deadline->assignees->count() > 3)
                                        <div class="w-6 h-6 rounded-full bg-gray-100 border-2 border-white flex items-center justify-center">
                                            <span class="text-xs text-gray-600">+{{ $deadline->assignees->count() - 3 }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path>
                        </svg>
                        <p class="text-sm text-gray-500 mt-2">No upcoming deadlines</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Chart.js Integration -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize all charts
    initializeProgressChart();
    initializeBudgetChart();
    initializeTeamProductivityChart();
    initializeActivityChart();
});

// Progress Chart
function initializeProgressChart() {
    const ctx = document.getElementById('progressChart');
    if (!ctx) return;
    
    const data = @json($progressChartData);
    
    new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100,
                    ticks: {
                        callback: function(value) {
                            return value + '%';
                        }
                    }
                }
            },
            elements: {
                point: {
                    radius: 4,
                    hoverRadius: 6
                }
            }
        }
    });
}

// Budget Chart
function initializeBudgetChart() {
    const ctx = document.getElementById('budgetChart');
    if (!ctx) return;
    
    const data = @json($budgetChartData);
    
    new Chart(ctx, {
        type: 'doughnut',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}

// Team Productivity Chart
function initializeTeamProductivityChart() {
    const ctx = document.getElementById('teamProductivityChart');
    if (!ctx) return;
    
    const data = @json($teamProductivityData);
    
    new Chart(ctx, {
        type: 'bar',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}

// Activity Chart
function initializeActivityChart() {
    const ctx = document.getElementById('activityChart');
    if (!ctx) return;
    
    const data = @json($activityTimelineData);
    
    new Chart(ctx, {
        type: 'line',
        data: data,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            },
            elements: {
                point: {
                    radius: 3,
                    hoverRadius: 5
                }
            }
        }
    });
}

// Livewire hook to refresh charts when data updates
document.addEventListener('livewire:navigated', function() {
    setTimeout(function() {
        initializeProgressChart();
        initializeBudgetChart();
        initializeTeamProductivityChart();
        initializeActivityChart();
    }, 100);
});
</script>