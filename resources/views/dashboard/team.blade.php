<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Team Management - AdCompro</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Header -->
    <header class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center space-x-4">
                    <a href="{{ route('dashboard') }}" class="text-gray-500 hover:text-gray-700">
                        ← Dashboard
                    </a>
                    <h1 class="text-2xl font-bold text-gray-900">👥 Team Management</h1>
                </div>
                <div class="text-sm text-gray-500">
                    {{ now()->format('l, F j, Y') }}
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Team Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <!-- Total Members -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <span class="text-blue-600 font-semibold">👥</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Members</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $teamStats['total'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Active Members -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <span class="text-green-600 font-semibold">✅</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Active Members</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $teamStats['active'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Project Assignments -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <span class="text-purple-600 font-semibold">📊</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Project Assignments</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $teamStats['projects'] ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <!-- Avg Projects/Member -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center">
                            <span class="text-orange-600 font-semibold">📈</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Avg Projects/Member</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $teamStats['avgProjects'] ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Team Members List -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200">
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <h2 class="text-lg font-semibold text-gray-900">Team Members</h2>
                    <button class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                        + Invite Member
                    </button>
                </div>
            </div>

            <div class="p-6">
                @if($allTeamMembers && count($allTeamMembers) > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($allTeamMembers as $member)
                            <div class="bg-gray-50 rounded-lg p-6 hover:bg-gray-100 transition-colors">
                                <div class="flex items-center space-x-4">
                                    <!-- Avatar -->
                                    <div class="flex-shrink-0">
                                        <div class="w-12 h-12 bg-orange-100 rounded-full flex items-center justify-center">
                                            <span class="text-orange-600 font-medium">
                                                {{ strtoupper(substr($member->name ?? 'U', 0, 1)) }}{{ strtoupper(substr(explode(' ', $member->name ?? 'User')[1] ?? '', 0, 1)) }}
                                            </span>
                                        </div>
                                    </div>
                                    
                                    <!-- Member Info -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="text-lg font-medium text-gray-900 truncate">
                                            {{ $member->name ?? 'Unknown User' }}
                                        </h3>
                                        <p class="text-sm text-gray-600 truncate">
                                            {{ $member->email ?? 'No email' }}
                                        </p>
                                        <div class="flex items-center mt-2 space-x-4">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                Active
                                            </span>
                                            <span class="text-xs text-gray-500">
                                                Joined {{ $member->created_at ? $member->created_at->format('M j, Y') : 'Unknown' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Member Details -->
                                <div class="mt-4 pt-4 border-t border-gray-200">
                                    <div class="grid grid-cols-2 gap-4 text-sm">
                                        <div>
                                            <span class="text-gray-500">ID:</span>
                                            <span class="font-medium text-gray-900">{{ $member->id ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-500">Status:</span>
                                            <span class="font-medium text-green-600">Active</span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons -->
                                <div class="mt-4 flex space-x-2">
                                    <button class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded text-sm font-medium">
                                        View Profile
                                    </button>
                                    <button class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-2 rounded text-sm font-medium">
                                        Settings
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <!-- Empty State -->
                    <div class="text-center py-12">
                        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <span class="text-4xl text-gray-400">👥</span>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2">No Team Members Yet</h3>
                        <p class="text-gray-600 mb-6">Invite team members to collaborate on projects and share workload.</p>
                        <button class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                            <span class="mr-2">+</span>
                            Invite Your First Team Member
                        </button>
                    </div>
                @endif
            </div>
        </div>

        <!-- Team Insights -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Recent Activity -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📈 Team Activity</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-600">Projects Created</span>
                        <span class="text-sm font-medium text-gray-900">{{ $teamStats['projects'] ?? 0 }} this month</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-600">Active Members</span>
                        <span class="text-sm font-medium text-gray-900">{{ $teamStats['active'] ?? 0 }} online</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-600">Collaboration Rate</span>
                        <span class="text-sm font-medium text-green-600">{{ $teamStats['avgProjects'] ?? 0 }}x avg</span>
                    </div>
                </div>
            </div>

            <!-- Team Performance -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">🎯 Performance</h3>
                <div class="space-y-4">
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-600">Team Productivity</span>
                        <span class="text-sm font-medium text-green-600">85% efficiency</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-600">Project Completion</span>
                        <span class="text-sm font-medium text-blue-600">92% on time</span>
                    </div>
                    <div class="flex items-center justify-between py-2">
                        <span class="text-sm text-gray-600">Collaboration Score</span>
                        <span class="text-sm font-medium text-purple-600">A+ rating</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Back to Dashboard -->
        <div class="mt-8 text-center">
            <a href="{{ route('dashboard') }}" 
               class="inline-flex items-center px-6 py-3 border border-gray-300 rounded-md text-gray-700 bg-white hover:bg-gray-50 font-medium">
                ← Back to Dashboard
            </a>
        </div>
    </div>
</body>
</html>