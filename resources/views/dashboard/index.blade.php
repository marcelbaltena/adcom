<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Projects - Project Manager</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Navigation Bar -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <h1 class="text-xl font-bold text-gray-900">📊 Project Manager</h1>
                    </div>
                    
                    <!-- Navigation Links -->
                    <div class="hidden md:ml-6 md:flex md:space-x-8">
                        <a href="{{ route('dashboard') }}" 
                           class="border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Dashboard
                        </a>
                        <a href="{{ route('projects.index') }}" 
                           class="border-indigo-500 text-gray-900 inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            Projects
                        </a>
                    </div>
                </div>
                
                <!-- User menu -->
                <div class="flex items-center">
                    <span class="text-sm text-gray-700">Welcome back!</span>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900">Projects</h2>
                <p class="text-gray-600">Manage your projects and track progress</p>
            </div>
            <a href="{{ route('projects.create') }}" 
               class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-md text-sm font-medium">
                + New Project
            </a>
        </div>

        <!-- Flash Messages -->
        @if(session('success'))
            <div class="mb-6 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-6 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Projects Grid -->
        @if(isset($projects) && $projects->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($projects as $project)
                    <div class="bg-white overflow-hidden shadow rounded-lg hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <!-- Project Header -->
                            <div class="flex items-center justify-between mb-4">
                                <h3 class="text-lg font-medium text-gray-900 truncate">
                                    {{ $project->name }}
                                </h3>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                    @if($project->status === 'active') bg-green-100 text-green-800
                                    @elseif($project->status === 'draft') bg-yellow-100 text-yellow-800
                                    @elseif($project->status === 'completed') bg-blue-100 text-blue-800
                                    @else bg-gray-100 text-gray-800
                                    @endif">
                                    {{ ucfirst($project->status) }}
                                </span>
                            </div>

                            <!-- Project Description -->
                            <p class="text-gray-600 text-sm mb-4">
                                {{ $project->description ? substr($project->description, 0, 100) . '...' : 'No description provided' }}
                            </p>

                            <!-- Project Meta -->
                            <div class="text-sm text-gray-500 space-y-1 mb-4">
                                <div class="flex justify-between">
                                    <span>Created:</span>
                                    <span>{{ $project->created_at ? $project->created_at->format('M j, Y') : 'Unknown' }}</span>
                                </div>
                                @if($project->start_date)
                                    <div class="flex justify-between">
                                        <span>Start Date:</span>
                                        <span>{{ $project->start_date->format('M j, Y') }}</span>
                                    </div>
                                @endif
                                @if($project->budget)
                                    <div class="flex justify-between">
                                        <span>Budget:</span>
                                        <span>
                                            @if($project->budget_type === 'time')
                                                {{ $project->budget }} hrs
                                            @else
                                                €{{ number_format($project->budget, 2) }}
                                            @endif
                                        </span>
                                    </div>
                                @endif
                            </div>

                            <!-- Action Buttons -->
                            <div class="flex space-x-2">
                                <a href="{{ route('projects.show', $project) }}" 
                                   class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-800 px-3 py-2 rounded text-sm font-medium text-center">
                                    View Details
                                </a>
                                @if(Route::has('projects.milestones'))
                                    <a href="{{ route('projects.milestones', $project) }}" 
                                       class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white px-3 py-2 rounded text-sm font-medium text-center">
                                        Milestones
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-12">
                <div class="w-24 h-24 mx-auto bg-gray-100 rounded-full flex items-center justify-center mb-6">
                    <span class="text-4xl text-gray-400">📋</span>
                </div>
                <h3 class="text-xl font-medium text-gray-900 mb-2">No projects yet</h3>
                <p class="text-gray-600 mb-6">Get started by creating your first project to organize your work.</p>
                <a href="{{ route('projects.create') }}" 
                   class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                    <span class="mr-2">+</span>
                    Create Your First Project
                </a>
            </div>
        @endif
    </div>
</body>
</html>