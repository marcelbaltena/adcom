@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gebruikersrechten Beheer</h1>
                    <p class="mt-1 text-sm text-gray-600">Beheer individuele gebruikersrechten en rollen</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Terug naar Gebruikers
                    </a>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <form method="GET" action="{{ route('admin.users.permissions') }}" class="space-y-4">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <div>
                        <label for="search" class="block text-sm font-medium text-gray-700 mb-1">Zoeken</label>
                        <input type="text" 
                               name="search" 
                               id="search"
                               class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" 
                               placeholder="Naam of email..." 
                               value="{{ request('search') }}">
                    </div>
                    <div>
                        <label for="company_id" class="block text-sm font-medium text-gray-700 mb-1">Bedrijf</label>
                        <select name="company_id" 
                                id="company_id"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Alle bedrijven</option>
                            @foreach($companies as $company)
                                <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                    {{ $company->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                        <select name="role" 
                                id="role"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Alle rollen</option>
                            <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                            <option value="beheerder" {{ request('role') == 'beheerder' ? 'selected' : '' }}>Beheerder</option>
                            <option value="account_manager" {{ request('role') == 'account_manager' ? 'selected' : '' }}>Account Manager</option>
                            <option value="user" {{ request('role') == 'user' ? 'selected' : '' }}>Gebruiker</option>
                        </select>
                    </div>
                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" 
                                id="status"
                                class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <option value="">Alle</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Actief</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactief</option>
                        </select>
                    </div>
                    <div class="flex items-end space-x-2">
                        <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-filter mr-2"></i>
                            Filter
                        </button>
                        <a href="{{ route('admin.users.permissions') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-times"></i>
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Gebruiker
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Bedrijf
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Rol
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Permissions
                            </th>
                            <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Status
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Acties</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $user->name }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ $user->email }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">{{ $user->company->name ?? '-' }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->role == 'admin')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="fas fa-crown mr-1"></i> Admin
                                        </span>
                                    @elseif($user->role == 'beheerder')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="fas fa-user-cog mr-1"></i> Beheerder
                                        </span>
                                    @elseif($user->role == 'account_manager')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="fas fa-briefcase mr-1"></i> Account Manager
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <i class="fas fa-user mr-1"></i> Gebruiker
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($user->role === 'admin')
                                        <span class="inline-flex items-center text-sm text-red-600">
                                            <i class="fas fa-infinity mr-1"></i> Volledige toegang
                                        </span>
                                    @else
                                        @php
                                            $permissionCount = is_array($user->permissions) ? count($user->permissions) : 0;
                                        @endphp
                                        <span class="text-sm text-gray-600">
                                            {{ $permissionCount }} permissions
                                        </span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
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
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('admin.users.permissions.edit', $user) }}" 
                                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                        <i class="fas fa-user-shield mr-1"></i>
                                        Rechten
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center">
                                    <div class="text-gray-400">
                                        <i class="fas fa-users fa-3x mb-4"></i>
                                        <p class="text-lg font-medium">Geen gebruikers gevonden</p>
                                        <p class="text-sm">Pas je filters aan of voeg nieuwe gebruikers toe</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($users->hasPages())
                <div class="bg-white px-4 py-3 border-t border-gray-200 sm:px-6">
                    {{ $users->withQueryString()->links() }}
                </div>
            @endif
        </div>

        <!-- Role Overview -->
        <div class="mt-6 bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Rol Overzicht</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="border rounded-lg p-4 bg-red-50 border-red-200">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-crown text-red-600 mr-2"></i>
                        <h4 class="font-semibold text-red-900">Admin</h4>
                    </div>
                    <p class="text-sm text-red-700">Volledige toegang tot het systeem</p>
                </div>
                <div class="border rounded-lg p-4 bg-yellow-50 border-yellow-200">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-user-cog text-yellow-600 mr-2"></i>
                        <h4 class="font-semibold text-yellow-900">Beheerder</h4>
                    </div>
                    <p class="text-sm text-yellow-700">Kan gebruikers en projecten beheren</p>
                </div>
                <div class="border rounded-lg p-4 bg-blue-50 border-blue-200">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-briefcase text-blue-600 mr-2"></i>
                        <h4 class="font-semibold text-blue-900">Account Manager</h4>
                    </div>
                    <p class="text-sm text-blue-700">Kan klanten en projecten beheren</p>
                </div>
                <div class="border rounded-lg p-4 bg-gray-50 border-gray-200">
                    <div class="flex items-center mb-2">
                        <i class="fas fa-user text-gray-600 mr-2"></i>
                        <h4 class="font-semibold text-gray-900">Gebruiker</h4>
                    </div>
                    <p class="text-sm text-gray-700">Basis toegang tot toegewezen projecten</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection