<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Welcome Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-semibold mb-2">
                        Welkom terug, {{ Auth::user()->name }}!
                    </h3>
                    <p class="text-gray-600">
                        Je bent ingelogd als: <span class="font-semibold">{{ Auth::user()->role }}</span>
                        @if(Auth::user()->company)
                            bij <span class="font-semibold">{{ Auth::user()->company->name }}</span>
                        @endif
                    </p>
                </div>
            </div>

            <!-- Debug Info Card -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Debug Informatie</h3>
                    <div class="space-y-2">
                        <p><strong>User ID:</strong> {{ Auth::id() }}</p>
                        <p><strong>Role:</strong> {{ Auth::user()->role }}</p>
                        <p><strong>Company ID:</strong> {{ Auth::user()->company_id ?? 'Geen' }}</p>
                        <p><strong>Is Active:</strong> {{ Auth::user()->is_active ? 'Ja' : 'Nee' }}</p>
                    </div>
                    
                    <h4 class="font-semibold mt-4 mb-2">Jouw Permissions:</h4>
                    <ul class="list-disc list-inside space-y-1">
                        @php
                            $permissions = \App\Models\RolePermission::getPermissionsForRole(Auth::user()->role);
                        @endphp
                        @forelse($permissions as $permission)
                            <li class="text-sm">{{ $permission['permission'] }} - {{ $permission['resource'] }} - {{ $permission['action'] }}</li>
                        @empty
                            <li class="text-sm text-gray-500">Geen specifieke permissions gevonden</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Test Links -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Test Links</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('projects.index') }}" class="text-center p-4 border rounded-lg hover:bg-gray-50 transition">
                            <span class="text-sm">Projecten</span>
                        </a>
                        <a href="{{ route('customers.index') }}" class="text-center p-4 border rounded-lg hover:bg-gray-50 transition">
                            <span class="text-sm">Klanten</span>
                        </a>
                        <a href="{{ route('companies.index') }}" class="text-center p-4 border rounded-lg hover:bg-gray-50 transition">
                            <span class="text-sm">Bedrijven</span>
                        </a>
                        @if(Auth::user()->role === 'admin')
                        <a href="{{ route('users.permissions.index') }}" class="text-center p-4 border rounded-lg hover:bg-gray-50 transition">
                            <span class="text-sm">Gebruikersbeheer</span>
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>