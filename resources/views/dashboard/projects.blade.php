<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Projects - AdCompro</title>
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
                    <h1 class="text-2xl font-bold text-gray-900">All Projects</h1>
                </div>
                <a href="{{ route('dashboard.create-project') }}" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium">
                    + New Project
                </a>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <!-- Success Message -->
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-md mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-md mb-6">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Project Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-blue-100 rounded-lg flex items-center justify-center">
                            <span class="text-blue-600 font-semibold">📊</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Total Projects</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $totalProjects ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center">
                            <span class="text-green-600 font-semibold">🚀</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Active</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $activeProjects ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-yellow-100 rounded-lg flex items-center justify-center">
                            <span class="text-yellow-600 font-semibold">📋</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">In Planning</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $planningProjects ?? 0 }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <div class="flex items-center">
                    <div class="flex-shrink-0">
                        <div class="w-8 h-8 bg-purple-100 rounded-lg flex items-center justify-center">
                            <span class="text-purple-600 font-semibold">✅</span>
                        </div>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-gray-600">Completed</p>
                        <p class="text-2xl font-bold text-gray-900">{{ $completedProjects ?? 0 }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Projects Grid -->
        @if($projects && $projects->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($projects as $project)
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 hover:shadow-md transition-shadow">
                        <!-- Project Header -->
                        <div class="p-6">
                            <div class="flex items-start justify-between mb-4">
                                <h3 class="text-lg font-semibold text-gray-900 flex-1">
                                    {{ $project->name }}
                                </h3>
                                <span class="ml-2 px-2 py-1 text-xs font-semibold rounded-full
                                    @if($project->status === 'active') bg-green-100 text-green-800
                                    @elseif($project->status === 'planning') bg-yellow-100 text-yellow-800
                                    @elseif($project->status === 'completed') bg-purple-100 text-purple-800
                                    @elseif($project->status === 'on_hold') bg-gray-100 text-gray-800
                                    @else bg-orange-100 text-orange-800
                                    @endif
                                ">
                                    @if($project->status === 'active') 🚀 Active
                                    @elseif($project->status === 'planning') 📋 Planning
                                    @elseif($project->status === 'completed') ✅ Completed
                                    @elseif($project->status === 'on_hold') ⏸️ On Hold
                                    @else 📝 {{ ucfirst($project->status) }}
                                    @endif
                                </span>
                            </div>

                            <p class="text-gray-600 text-sm mb-4">
                                {{ $project->description ?? 'No description provided' }}
                            </p>

                            <!-- Project Meta -->
                            <div class="space-y-2 text-sm text-gray-500 mb-6">
                                <div class="flex justify-between">
                                    <span>Started:</span>
                                    <span>{{ $project->start_date ? $project->start_date->format('M j, Y') : 'Not set' }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Budget:</span>
                                    <span>
                                        @if($project->budget_type === 'financial')
                                            €{{ number_format($project->budget, 2) }}
                                        @else
                                            {{ $project->budget }} hrs
                                        @endif
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span>Created:</span>
                                    <span>{{ $project->created_at ? $project->created_at->format('M j, Y') : 'Unknown' }}</span>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex space-x-2">
                                <a href="{{ route('projects.milestones', $project->id) }}" 
                                   class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded text-sm font-medium text-center">
                                    Manage Milestones
                                </a>
                                <a href="{{ route('dashboard.edit-project', $project->id) }}" 
                                   class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-3 py-2 rounded text-sm font-medium">
                                    Edit
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-12 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <span class="text-4xl text-gray-400">📋</span>
                </div>
                <h3 class="text-xl font-semibold text-gray-900 mb-2">No Projects Yet</h3>
                <p class="text-gray-600 mb-6">Get started by creating your first project to organize your work and track progress.</p>
                <a href="{{ route('dashboard.create-project') }}" 
                   class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    <span class="mr-2">+</span>
                    Create Your First Project
                </a>
            </div>
        @endif
    </div>
</body>
</html>