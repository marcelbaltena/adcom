<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-user-shield mr-2"></i>Gebruikers & Rechten Beheer
            </h2>
            <div class="flex space-x-3">
                <a href="{{ route('admin.roles') }}" 
                   class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
                    <i class="fas fa-lock mr-2"></i>Rol Rechten
                </a>
                <a href="{{ route('admin.users.permissions.export') }}" 
                   class="bg-green-600 hover:bg-green-700 text-white font-medium py-2 px-4 rounded inline-flex items-center">
                    <i class="fas fa-download mr-2"></i>Exporteer
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.users.permissions.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Zoeken</label>
                            <input type="text" name="search" value="{{ request('search') }}" 
                                   placeholder="Naam of email..."
                                   class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Bedrijf</label>
                            <select name="company_id" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Alle bedrijven</option>
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}" {{ request('company_id') == $company->id ? 'selected' : '' }}>
                                        {{ $company->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rol</label>
                            <select name="role" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Alle rollen</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role }}" {{ request('role') == $role ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $role)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="is_active" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                <option value="">Alle</option>
                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Actief</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Inactief</option>
                            </select>
                        </div>
                        
                        <div class="md:col-span-4 flex gap-2">
                            <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                                <i class="fas fa-search mr-1"></i> Filteren
                            </button>
                            <a href="{{ route('admin.users.permissions.index') }}" 
                               class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded">
                                <i class="fas fa-times mr-1"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Gebruiker
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Bedrijf
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Rol
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Projecten
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Speciale Rechten
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Acties
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($users as $user)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                                <div class="text-sm text-gray-500">{{ $user->email }}</div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $user->company->name ?? '-' }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                @if($user->role === 'admin') bg-red-100 text-red-800
                                                @elseif($user->role === 'beheerder') bg-purple-100 text-purple-800
                                                @elseif($user->role === 'account_manager') bg-blue-100 text-blue-800
                                                @else bg-gray-100 text-gray-800
                                                @endif">
                                                {{ ucfirst(str_replace('_', ' ', $user->role)) }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ $user->projectTeams->count() }} projecten
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            <div class="flex space-x-2">
                                                @if($user->can_see_all_projects)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-green-100 text-green-800">
                                                        <i class="fas fa-eye mr-1"></i>Alle projecten
                                                    </span>
                                                @endif
                                                @if($user->can_see_financial_data)
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-yellow-100 text-yellow-800">
                                                        <i class="fas fa-euro-sign mr-1"></i>Financiën
                                                    </span>
                                                @endif
                                                @if(!$user->can_see_all_projects && !$user->can_see_financial_data)
                                                    <span class="text-gray-400">-</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <button onclick="toggleUserStatus({{ $user->id }})" 
                                                    class="toggle-status-btn cursor-pointer"
                                                    data-user-id="{{ $user->id }}">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                    {{ $user->is_active ? 'Actief' : 'Inactief' }}
                                                </span>
                                            </button>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <a href="{{ route('admin.users.permissions.edit', $user) }}" 
                                               class="text-indigo-600 hover:text-indigo-900">
                                                <i class="fas fa-edit"></i> Bewerken
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                            Geen gebruikers gevonden
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                @if($users->hasPages())
                    <div class="px-6 py-4 border-t bg-gray-50">
                        {{ $users->links() }}
                    </div>
                @endif
            </div>

            <!-- Bulk Actions -->
            @if($users->count() > 0)
            <div class="mt-6 bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Bulk Acties</h3>
                    <form id="bulk-form" method="POST" action="{{ route('admin.users.permissions.bulk-update') }}" class="flex items-end space-x-4">
                        @csrf
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Selecteer gebruikers</label>
                            <p class="text-xs text-gray-500 mb-2">Houd Ctrl/Cmd ingedrukt om meerdere te selecteren</p>
                            <select name="user_ids[]" multiple class="w-full rounded-md border-gray-300 shadow-sm" size="5">
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Actie</label>
                            <select name="action" id="bulk-action" class="rounded-md border-gray-300 shadow-sm">
                                <option value="">Kies actie...</option>
                                <option value="activate">Activeren</option>
                                <option value="deactivate">Deactiveren</option>
                                <option value="change_role">Rol wijzigen</option>
                                <option value="change_company">Bedrijf wijzigen</option>
                            </select>
                        </div>
                        
                        <div id="bulk-role-select" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nieuwe rol</label>
                            <select name="role" class="rounded-md border-gray-300 shadow-sm">
                                <option value="user">User</option>
                                <option value="account_manager">Account Manager</option>
                                <option value="beheerder">Beheerder</option>
                            </select>
                        </div>
                        
                        <div id="bulk-company-select" class="hidden">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nieuw bedrijf</label>
                            <select name="company_id" class="rounded-md border-gray-300 shadow-sm">
                                @foreach($companies as $company)
                                    <option value="{{ $company->id }}">{{ $company->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                            Uitvoeren
                        </button>
                    </form>
                </div>
            </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script>
        function toggleUserStatus(userId) {
            if (!confirm('Weet je zeker dat je deze gebruiker wilt activeren/deactiveren?')) {
                return;
            }

            fetch(`/admin/users/permissions/${userId}/toggle-active`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Er is een fout opgetreden');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Er is een fout opgetreden');
            });
        }

        // Bulk action handling
        document.getElementById('bulk-action')?.addEventListener('change', function() {
            document.getElementById('bulk-role-select').classList.add('hidden');
            document.getElementById('bulk-company-select').classList.add('hidden');
            
            if (this.value === 'change_role') {
                document.getElementById('bulk-role-select').classList.remove('hidden');
            } else if (this.value === 'change_company') {
                document.getElementById('bulk-company-select').classList.remove('hidden');
            }
        });

        // Bulk form submission
        document.getElementById('bulk-form')?.addEventListener('submit', function(e) {
            const selectedUsers = this.querySelector('select[name="user_ids[]"]').selectedOptions;
            if (selectedUsers.length === 0) {
                e.preventDefault();
                alert('Selecteer minimaal één gebruiker');
                return;
            }

            const action = this.querySelector('select[name="action"]').value;
            if (!action) {
                e.preventDefault();
                alert('Selecteer een actie');
                return;
            }

            if (!confirm(`Weet je zeker dat je deze actie wilt uitvoeren op ${selectedUsers.length} gebruiker(s)?`)) {
                e.preventDefault();
            }
        });
    </script>
    @endpush
</x-app-layout>