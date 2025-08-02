@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gebruikersrechten Bewerken</h1>
                    <p class="mt-1 text-sm text-gray-600">Beheer rechten voor {{ $user->name }}</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('admin.users.permissions') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Terug naar overzicht
                    </a>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('admin.users.permissions.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- User Info & Role -->
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Gebruiker Informatie</h2>
                        
                        <!-- User Avatar -->
                        <div class="flex items-center mb-4">
                            <div class="h-16 w-16 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-xl">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                            <div class="ml-4">
                                <h3 class="text-lg font-medium text-gray-900">{{ $user->name }}</h3>
                                <p class="text-sm text-gray-500">{{ $user->email }}</p>
                            </div>
                        </div>

                        <div class="border-t pt-4">
                            <dl class="space-y-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Bedrijf</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $user->company->name ?? 'Geen bedrijf' }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Status</dt>
                                    <dd class="mt-1">
                                        @if($user->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <span class="w-2 h-2 mr-1.5 bg-green-400 rounded-full"></span>
                                                Actief
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                                <span class="w-2 h-2 mr-1.5 bg-red-400 rounded-full"></span>
                                                Inactief
                                            </span>
                                        @endif
                                    </dd>
                                </div>
                            </dl>
                        </div>

                        <!-- Role Selection -->
                        <div class="mt-6 border-t pt-6">
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-2">Gebruikersrol</label>
                            <select name="role" id="role" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin - Volledige toegang</option>
                                <option value="beheerder" {{ $user->role == 'beheerder' ? 'selected' : '' }}>Beheerder</option>
                                <option value="account_manager" {{ $user->role == 'account_manager' ? 'selected' : '' }}>Account Manager</option>
                                <option value="user" {{ $user->role == 'user' ? 'selected' : '' }}>Gebruiker</option>
                            </select>
                            <p class="mt-1 text-xs text-gray-500">Admin gebruikers hebben automatisch alle rechten</p>
                        </div>
                    </div>
                </div>

                <!-- Permissions -->
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">Specifieke Rechten</h2>
                        
                        <div id="permissions-container" class="space-y-6">
                            @foreach($permissionCategories as $category => $permissions)
                                <div class="border rounded-lg p-4">
                                    <h3 class="font-medium text-gray-900 mb-3 flex items-center">
                                        @if($category == 'Projecten')
                                            <i class="fas fa-project-diagram mr-2 text-indigo-500"></i>
                                        @elseif($category == 'Klanten')
                                            <i class="fas fa-users mr-2 text-green-500"></i>
                                        @elseif($category == 'Gebruikers')
                                            <i class="fas fa-user-cog mr-2 text-blue-500"></i>
                                        @elseif($category == 'Bedrijven')
                                            <i class="fas fa-building mr-2 text-purple-500"></i>
                                        @elseif($category == 'Rapportages')
                                            <i class="fas fa-chart-bar mr-2 text-orange-500"></i>
                                        @elseif($category == 'Templates')
                                            <i class="fas fa-file-alt mr-2 text-pink-500"></i>
                                        @endif
                                        {{ $category }}
                                    </h3>
                                    <div class="space-y-2">
                                        @foreach($permissions as $permission => $label)
                                            <label class="flex items-center space-x-3 text-sm">
                                                <input type="checkbox" 
                                                       name="permissions[]" 
                                                       value="{{ $permission }}"
                                                       {{ in_array($permission, $userPermissions) ? 'checked' : '' }}
                                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                                <span class="text-gray-700">{{ $label }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-md">
                            <p class="text-sm text-yellow-800">
                                <i class="fas fa-info-circle mr-1"></i>
                                Deze rechten zijn alleen van toepassing als de rol niet "Admin" is
                            </p>
                        </div>
                    </div>

                    <!-- Project Access -->
                    @if($projects->count() > 0)
                        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mt-6">
                            <h2 class="text-lg font-semibold text-gray-900 mb-4">Project Toegang</h2>
                            
                            <div class="space-y-3">
                                @foreach($projects as $project)
                                    <div class="flex items-center justify-between border rounded-lg p-3 hover:bg-gray-50">
                                        <div class="flex-1">
                                            <h4 class="font-medium text-gray-900">{{ $project->name }}</h4>
                                            @if($project->customer)
                                                <p class="text-sm text-gray-500">Klant: {{ $project->customer->name }}</p>
                                            @endif
                                        </div>
                                        <div>
                                            <select name="project_access[{{ $project->id }}]" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm">
                                                <option value="">Geen toegang</option>
                                                <option value="reviewer" {{ isset($projectAccess[$project->id]) && $projectAccess[$project->id] == 'reviewer' ? 'selected' : '' }}>
                                                    Reviewer - Alleen bekijken
                                                </option>
                                                <option value="assignee" {{ isset($projectAccess[$project->id]) && $projectAccess[$project->id] == 'assignee' ? 'selected' : '' }}>
                                                    Assignee - Kan taken uitvoeren
                                                </option>
                                                <option value="owner" {{ isset($projectAccess[$project->id]) && $projectAccess[$project->id] == 'owner' ? 'selected' : '' }}>
                                                    Owner - Volledige controle
                                                </option>
                                            </select>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="mt-6 flex items-center justify-end space-x-3">
                <a href="{{ route('admin.users.permissions') }}" class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    Annuleren
                </a>
                <button type="submit" class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <i class="fas fa-save mr-2"></i>
                    Rechten Opslaan
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Disable permissions checkboxes when admin role is selected
    document.getElementById('role').addEventListener('change', function() {
        const isAdmin = this.value === 'admin';
        const checkboxes = document.querySelectorAll('#permissions-container input[type="checkbox"]');
        
        checkboxes.forEach(checkbox => {
            checkbox.disabled = isAdmin;
            if (isAdmin) {
                checkbox.classList.add('opacity-50', 'cursor-not-allowed');
            } else {
                checkbox.classList.remove('opacity-50', 'cursor-not-allowed');
            }
        });
        
        // Update the warning message
        const container = document.getElementById('permissions-container');
        if (isAdmin) {
            container.classList.add('opacity-50');
        } else {
            container.classList.remove('opacity-50');
        }
    });
    
    // Trigger on page load
    document.getElementById('role').dispatchEvent(new Event('change'));
</script>
@endpush
@endsection