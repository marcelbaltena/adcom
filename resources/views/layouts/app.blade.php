<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @livewireStyles
    
    <!-- Additional Styles -->
    @yield('styles')
    @stack('styles')
</head>
<body class="font-sans antialiased bg-gray-50">
    <!-- Navigation -->
    <nav class="bg-white shadow-sm border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <!-- Logo -->
                    <div class="flex-shrink-0">
                        <a href="{{ route('dashboard') }}" class="flex items-center">
                            <h1 class="text-xl font-bold text-gray-900">
                                <i class="fas fa-chart-line mr-2 text-blue-600"></i>{{ config('app.name', 'AdComPro') }}
                            </h1>
                        </a>
                    </div>
                    
                    <!-- Navigation Links -->
                    <div class="hidden md:ml-6 md:flex md:space-x-6">
                        <a href="{{ route('dashboard') }}" 
                           class="@if(request()->routeIs('dashboard')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-home mr-1"></i> Dashboard
                        </a>
                        
                        @if(\App\Models\RolePermission::roleHasPermission(auth()->user()->role ?? 'user', 'manage', 'projects', 'view'))
                        <a href="{{ route('projects.index') }}" 
                           class="@if(request()->routeIs('projects.*') && !request()->routeIs('project-templates.*')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-project-diagram mr-1"></i> Projecten
                        </a>
                        @endif
                        
                        @if(\App\Models\RolePermission::roleHasPermission(auth()->user()->role ?? 'user', 'manage', 'customers', 'view'))
                        <a href="{{ route('customers.index') }}" 
                           class="@if(request()->routeIs('customers.*')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-users mr-1"></i> Klanten
                        </a>
                        @endif
                        
                        @if(auth()->user() && auth()->user()->role === 'admin')
                        <a href="{{ route('companies.index') }}" 
                           class="@if(request()->routeIs('companies.*')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                            <i class="fas fa-building mr-1"></i> Bedrijven
                        </a>
                        @endif

                        <!-- Templates Dropdown -->
                        @if(\App\Models\RolePermission::roleHasPermission(auth()->user()->role ?? 'user', 'manage', 'templates', 'view'))
                        <div class="relative inline-flex items-center">
                            <button type="button" 
                                    onclick="toggleDropdown('templates-dropdown')"
                                    class="@if(request()->routeIs('service-templates.*') || request()->routeIs('project-templates.*')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                <i class="fas fa-file-alt mr-1"></i> Templates <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </button>
                            <div id="templates-dropdown" class="hidden absolute top-full left-0 mt-1 w-48 bg-white rounded-md shadow-lg z-10">
                                <a href="{{ route('service-templates.index') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-tags mr-2"></i> Service Catalog
                                </a>
                                <a href="{{ route('project-templates.index') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-clipboard-list mr-2"></i> Project Templates
                                </a>
                            </div>
                        </div>
                        @endif

                        <!-- Admin Menu -->
                        @if(auth()->user() && (auth()->user()->role === 'admin' || auth()->user()->role === 'beheerder'))
                        <div class="relative inline-flex items-center">
                            <button type="button" 
                                    onclick="toggleDropdown('admin-dropdown')"
                                    class="@if(request()->routeIs('admin.users.*')) border-indigo-500 text-gray-900 @else border-transparent text-gray-500 hover:border-gray-300 hover:text-gray-700 @endif inline-flex items-center px-1 pt-1 border-b-2 text-sm font-medium">
                                <i class="fas fa-cog mr-1"></i> Beheer <i class="fas fa-chevron-down ml-1 text-xs"></i>
                            </button>
                            <div id="admin-dropdown" class="hidden absolute top-full left-0 mt-1 w-64 bg-white rounded-md shadow-lg z-10">
                                <a href="{{ route('admin.users.index') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-users mr-2"></i> Gebruikers
                                </a>
                                <a href="{{ route('admin.users.permissions') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user-shield mr-2"></i> Gebruikersrechten
                                </a>
                                <a href="{{ route('admin.users.hours.index') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-clock mr-2"></i> Urenbeheer
                                </a>
                                <a href="{{ route('admin.users.activity-log') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-history mr-2"></i> Activity Log
                                </a>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
                
                <!-- User menu -->
                <div class="flex items-center space-x-4">
                    @auth
                        <!-- User Info -->
                        <div class="hidden md:block text-sm text-gray-700">
                            <span class="font-medium">{{ auth()->user()->name }}</span>
                            <span class="text-gray-500">({{ auth()->user()->role }})</span>
                            @if(auth()->user()->company)
                                <span class="text-gray-500">- {{ auth()->user()->company->name }}</span>
                            @endif
                        </div>
                        
                        <!-- User Dropdown -->
                        <div class="relative">
                            <button type="button" 
                                    onclick="toggleDropdown('user-dropdown')"
                                    class="flex items-center text-sm rounded-full focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                                <div class="h-8 w-8 rounded-full bg-indigo-500 flex items-center justify-center text-white">
                                    {{ substr(auth()->user()->name, 0, 1) }}
                                </div>
                            </button>
                            <div id="user-dropdown" class="hidden absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg z-10">
                                <a href="{{ route('profile.edit') }}" 
                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                    <i class="fas fa-user-cog mr-2"></i> Profiel
                                </a>
                                <hr class="my-1">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" 
                                            class="w-full text-left px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">
                                        <i class="fas fa-sign-out-alt mr-2"></i> Uitloggen
                                    </button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-gray-700 hover:text-gray-900">
                            <i class="fas fa-sign-in-alt mr-1"></i> Inloggen
                        </a>
                    @endauth
                </div>

                <!-- Mobile menu button -->
                <div class="md:hidden flex items-center">
                    <button id="mobile-menu-button" type="button" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-indigo-500">
                        <span class="sr-only">Open main menu</span>
                        <svg class="block h-6 w-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile menu -->
        <div class="md:hidden hidden" id="mobile-menu">
            <div class="pt-2 pb-3 space-y-1">
                <a href="{{ route('dashboard') }}" 
                   class="@if(request()->routeIs('dashboard')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-home mr-2"></i> Dashboard
                </a>
                
                @if(\App\Models\RolePermission::roleHasPermission(auth()->user()->role ?? 'user', 'manage', 'projects', 'view'))
                <a href="{{ route('projects.index') }}" 
                   class="@if(request()->routeIs('projects.*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-project-diagram mr-2"></i> Projecten
                </a>
                @endif
                
                @if(\App\Models\RolePermission::roleHasPermission(auth()->user()->role ?? 'user', 'manage', 'customers', 'view'))
                <a href="{{ route('customers.index') }}" 
                   class="@if(request()->routeIs('customers.*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-users mr-2"></i> Klanten
                </a>
                @endif
                
                @if(auth()->user() && auth()->user()->role === 'admin')
                <a href="{{ route('companies.index') }}" 
                   class="@if(request()->routeIs('companies.*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                    <i class="fas fa-building mr-2"></i> Bedrijven
                </a>
                @endif
                
                <!-- Templates Section -->
                @if(\App\Models\RolePermission::roleHasPermission(auth()->user()->role ?? 'user', 'manage', 'templates', 'view'))
                <div class="border-t border-gray-200 pt-2">
                    <div class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Templates</div>
                    <a href="{{ route('service-templates.index') }}" 
                       class="@if(request()->routeIs('service-templates.*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        <i class="fas fa-tags mr-2"></i> Service Catalog
                    </a>
                    <a href="{{ route('project-templates.index') }}" 
                       class="@if(request()->routeIs('project-templates.*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        <i class="fas fa-clipboard-list mr-2"></i> Project Templates
                    </a>
                </div>
                @endif

                @if(auth()->user() && (auth()->user()->role === 'admin' || auth()->user()->role === 'beheerder'))
                <!-- Admin Section -->
                <div class="border-t border-gray-200 pt-2">
                    <div class="px-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Beheer</div>
                    <a href="{{ route('admin.users.index') }}" 
                       class="@if(request()->routeIs('admin.users.index')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        <i class="fas fa-users mr-2"></i> Gebruikers
                    </a>
                    <a href="{{ route('admin.users.permissions') }}" 
                       class="@if(request()->routeIs('admin.users.permissions*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        <i class="fas fa-user-shield mr-2"></i> Gebruikersrechten
                    </a>
                    <a href="{{ route('admin.users.hours.index') }}" 
                       class="@if(request()->routeIs('admin.users.hours.*')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        <i class="fas fa-clock mr-2"></i> Urenbeheer
                    </a>
                    <a href="{{ route('admin.users.activity-log') }}" 
                       class="@if(request()->routeIs('admin.users.activity-log')) bg-indigo-50 border-indigo-500 text-indigo-700 @else border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 @endif block pl-3 pr-4 py-2 border-l-4 text-base font-medium">
                        <i class="fas fa-history mr-2"></i> Activity Log
                    </a>
                </div>
                @endif

                <!-- User Section -->
                @auth
                <div class="border-t border-gray-200 pt-2">
                    <div class="px-3 py-2">
                        <div class="text-base font-medium text-gray-800">{{ auth()->user()->name }}</div>
                        <div class="text-sm font-medium text-gray-500">{{ auth()->user()->email }}</div>
                        <div class="text-xs text-gray-400">{{ auth()->user()->role }} - {{ auth()->user()->company->name ?? 'Geen bedrijf' }}</div>
                    </div>
                    <a href="{{ route('profile.edit') }}" 
                       class="block pl-3 pr-4 py-2 border-l-4 border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 text-base font-medium">
                        <i class="fas fa-user-cog mr-2"></i> Profiel
                    </a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" 
                                class="w-full text-left block pl-3 pr-4 py-2 border-l-4 border-transparent text-gray-600 hover:bg-gray-50 hover:border-gray-300 hover:text-gray-800 text-base font-medium">
                            <i class="fas fa-sign-out-alt mr-2"></i> Uitloggen
                        </button>
                    </form>
                </div>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Page Heading -->
    @isset($header)
        <header class="bg-white shadow">
            <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                {{ $header }}
            </div>
        </header>
    @endisset

    <!-- Page Content -->
    <main>
        {{-- Support both component slot and traditional yield --}}
        @if(isset($slot))
            {{ $slot }}
        @else
            @yield('content')
        @endif
    </main>

    @livewireScripts
    
    <!-- Additional Scripts -->
    @yield('scripts')
    @stack('scripts')

    <!-- Scripts -->
    <script>
        // Mobile menu toggle
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuButton = document.getElementById('mobile-menu-button');
            const mobileMenu = document.getElementById('mobile-menu');
            
            if (mobileMenuButton && mobileMenu) {
                mobileMenuButton.addEventListener('click', function() {
                    mobileMenu.classList.toggle('hidden');
                });
            }
        });

        // Dropdown toggle function
        function toggleDropdown(dropdownId) {
            const dropdown = document.getElementById(dropdownId);
            const allDropdowns = document.querySelectorAll('[id$="-dropdown"]');
            
            // Close all other dropdowns
            allDropdowns.forEach(function(d) {
                if (d.id !== dropdownId) {
                    d.classList.add('hidden');
                }
            });
            
            // Toggle current dropdown
            dropdown.classList.toggle('hidden');
        }

        // Close dropdowns when clicking outside
        document.addEventListener('click', function(event) {
            const isDropdownButton = event.target.closest('button[onclick*="toggleDropdown"]');
            const isDropdownContent = event.target.closest('[id$="-dropdown"]');
            
            if (!isDropdownButton && !isDropdownContent) {
                const allDropdowns = document.querySelectorAll('[id$="-dropdown"]');
                allDropdowns.forEach(function(dropdown) {
                    dropdown.classList.add('hidden');
                });
            }
        });
    </script>
</body>
</html>