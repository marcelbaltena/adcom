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
                        Welkom terug!
                    </h3>
                    @auth
                        <p class="text-gray-600">
                            Je bent ingelogd als: <span class="font-semibold">{{ Auth::user()->name }}</span>
                            <br>
                            Role: <span class="font-semibold">{{ Auth::user()->role }}</span>
                            @if(Auth::user()->company)
                                <br>Bedrijf: <span class="font-semibold">{{ Auth::user()->company->name }}</span>
                            @endif
                        </p>
                    @endauth
                </div>
            </div>

            <!-- Quick Links -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-4">Snelle Links</h3>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <a href="{{ route('projects.index') }}" class="text-center p-4 border rounded-lg hover:bg-gray-50 transition">
                            <i class="fas fa-project-diagram text-2xl text-blue-600 mb-2 block"></i>
                            <span class="text-sm">Projecten</span>
                        </a>
                        <a href="{{ route('customers.index') }}" class="text-center p-4 border rounded-lg hover:bg-gray-50 transition">
                            <i class="fas fa-users text-2xl text-green-600 mb-2 block"></i>
                            <span class="text-sm">Klanten</span>
                        </a>
                        <a href="{{ route('companies.index') }}" class="text-center p-4 border rounded-lg hover:bg-gray-50 transition">
                            <i class="fas fa-building text-2xl text-purple-600 mb-2 block"></i>
                            <span class="text-sm">Bedrijven</span>
                        </a>
                        <a href="{{ route('service-templates.index') }}" class="text-center p-4 border rounded-lg hover:bg-gray-50 transition">
                            <i class="fas fa-tags text-2xl text-yellow-600 mb-2 block"></i>
                            <span class="text-sm">Service Catalog</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>