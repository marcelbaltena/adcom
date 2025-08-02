@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Gebruiker Bewerken</h1>
                    <p class="mt-1 text-sm text-gray-600">Wijzig gebruikersgegevens en toegang</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Terug naar overzicht
                    </a>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <!-- Basic Info -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Basis Informatie</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">
                                Naam <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name', $user->name) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('name') border-red-300 @enderror"
                                   required>
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="email" class="block text-sm font-medium text-gray-700 mb-1">
                                Email <span class="text-red-500">*</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   value="{{ old('email', $user->email) }}"
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('email') border-red-300 @enderror"
                                   required>
                            @error('email')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Password Reset -->
                <div class="mb-8 border-t pt-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Wachtwoord Wijzigen</h2>
                    <p class="text-sm text-gray-600 mb-4">Laat leeg om het huidige wachtwoord te behouden</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">
                                Nieuw Wachtwoord
                            </label>
                            <input type="password" 
                                   name="password" 
                                   id="password" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('password') border-red-300 @enderror">
                            @error('password')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">
                                Bevestig Wachtwoord
                            </label>
                            <input type="password" 
                                   name="password_confirmation" 
                                   id="password_confirmation" 
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                    </div>
                </div>

                <!-- Organization -->
                <div class="mb-8 border-t pt-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Organisatie & Rol</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="company_id" class="block text-sm font-medium text-gray-700 mb-1">
                                Bedrijf <span class="text-red-500">*</span>
                            </label>
                            <select name="company_id" 
                                    id="company_id" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('company_id') border-red-300 @enderror"
                                    required>
                                <option value="">Selecteer een bedrijf</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ old('company_id', $user->company_id) == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('company_id')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label for="role" class="block text-sm font-medium text-gray-700 mb-1">
                                Rol <span class="text-red-500">*</span>
                            </label>
                            <select name="role" 
                                    id="role" 
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 @error('role') border-red-300 @enderror"
                                    required>
                                <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>Gebruiker</option>
                                <option value="account_manager" {{ old('role', $user->role) == 'account_manager' ? 'selected' : '' }}>Account Manager</option>
                                <option value="beheerder" {{ old('role', $user->role) == 'beheerder' ? 'selected' : '' }}>Beheerder</option>
                                <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>Admin</option>
                            </select>
                            @error('role')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mt-6">
                        <label class="flex items-center">
                            <input type="checkbox" 
                                   name="is_active" 
                                   value="1"
                                   {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                                   class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2 text-sm text-gray-700">Account is actief</span>
                        </label>
                    </div>
                </div>

                <!-- Project Access -->
                <div class="mb-8 border-t pt-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">
                        Project Toegang
                        <span class="text-sm font-normal text-gray-500 ml-2">({{ count($userProjectIds) }} geselecteerd)</span>
                    </h2>
                    
                    <div class="bg-gray-50 rounded-lg p-4 max-h-64 overflow-y-auto">
                        @forelse($projects as $project)
                            <label class="flex items-center mb-3 hover:bg-white p-2 rounded">
                                <input type="checkbox" 
                                       name="project_ids[]" 
                                       value="{{ $project->id }}"
                                       {{ in_array($project->id, old('project_ids', $userProjectIds)) ? 'checked' : '' }}
                                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <span class="ml-3 text-sm">
                                    <span class="font-medium text-gray-900">{{ $project->name }}</span>
                                    @if($project->customer)
                                        <span class="text-gray-500"> - {{ $project->customer->name }}</span>
                                    @endif
                                </span>
                            </label>
                        @empty
                            <p class="text-sm text-gray-500">Geen projecten beschikbaar</p>
                        @endforelse
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-6 border-t">
                    <div>
                        <a href="{{ route('admin.users.permissions.edit', $user) }}" 
                           class="text-sm text-indigo-600 hover:text-indigo-900">
                            <i class="fas fa-user-shield mr-1"></i>
                            Geavanceerde rechten beheren
                        </a>
                    </div>
                    <div class="flex items-center space-x-3">
                        <a href="{{ route('admin.users.index') }}" 
                           class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Annuleren
                        </a>
                        <button type="submit" 
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <i class="fas fa-save mr-2"></i>
                            Wijzigingen Opslaan
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection