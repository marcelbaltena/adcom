<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <i class="fas fa-history mr-2"></i>Activity Log - Gebruikersbeheer
            </h2>
            <a href="{{ route('admin.users.index') }}" 
               class="bg-gray-600 hover:bg-gray-700 text-white font-medium py-2 px-4 rounded">
                <i class="fas fa-arrow-left mr-2"></i>Terug naar Gebruikers
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Filters -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <form method="GET" action="{{ route('admin.users.activity-log') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Van datum</label>
                            <input type="date" name="from_date" value="{{ request('from_date') }}" 
                                   class="w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tot datum</label>
                            <input type="date" name="to_date" value="{{ request('to_date') }}" 
                                   class="w-full rounded-md border-gray-300 shadow-sm">
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Uitgevoerd door</label>
                            <select name="user_id" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Alle gebruikers</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Actie</label>
                            <select name="action" class="w-full rounded-md border-gray-300 shadow-sm">
                                <option value="">Alle acties</option>
                                <option value="created" {{ request('action') == 'created' ? 'selected' : '' }}>Aangemaakt</option>
                                <option value="updated" {{ request('action') == 'updated' ? 'selected' : '' }}>Bijgewerkt</option>
                                <option value="deleted" {{ request('action') == 'deleted' ? 'selected' : '' }}>Verwijderd</option>
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">&nbsp;</label>
                            <div class="flex gap-2">
                                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-4 rounded">
                                    <i class="fas fa-search mr-1"></i> Filter
                                </button>
                                <a href="{{ route('admin.users.activity-log') }}" 
                                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-medium py-2 px-4 rounded">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Activity Log Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Datum/Tijd
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Gebruiker
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actie
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Beschrijving
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Wijzigingen
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($activities as $activity)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($activity->created_at)->format('d-m-Y H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $activity->user_name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                            @if($activity->action === 'created') bg-green-100 text-green-800
                                            @elseif($activity->action === 'updated') bg-blue-100 text-blue-800
                                            @elseif($activity->action === 'deleted') bg-red-100 text-red-800
                                            @else bg-gray-100 text-gray-800
                                            @endif">
                                            {{ ucfirst($activity->action) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-900">
                                        {{ $activity->description }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        @if($activity->old_values || $activity->new_values)
                                            <button onclick="showChanges({{ $activity->id }})" 
                                                    class="text-indigo-600 hover:text-indigo-900">
                                                <i class="fas fa-eye"></i> Bekijk
                                            </button>
                                            <div id="changes-{{ $activity->id }}" class="hidden mt-2 p-2 bg-gray-50 rounded text-xs">
                                                @if($activity->old_values)
                                                    @php $oldValues = json_decode($activity->old_values, true); @endphp
                                                    @if($oldValues && count($oldValues) > 0)
                                                        <div class="mb-2">
                                                            <strong>Oude waarden:</strong>
                                                            <ul class="mt-1">
                                                                @foreach($oldValues as $field => $value)
                                                                    <li>{{ $field }}: {{ is_array($value) ? json_encode($value) : $value }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                @endif
                                                
                                                @if($activity->new_values)
                                                    @php $newValues = json_decode($activity->new_values, true); @endphp
                                                    @if($newValues && count($newValues) > 0)
                                                        <div>
                                                            <strong>Nieuwe waarden:</strong>
                                                            <ul class="mt-1">
                                                                @foreach($newValues as $field => $value)
                                                                    <li>{{ $field }}: {{ is_array($value) ? json_encode($value) : $value }}</li>
                                                                @endforeach
                                                            </ul>
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-gray-400">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                        Geen activiteiten gevonden
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                @if($activities->hasPages())
                    <div class="px-6 py-4 border-t">
                        {{ $activities->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function showChanges(id) {
            const element = document.getElementById('changes-' + id);
            if (element.classList.contains('hidden')) {
                element.classList.remove('hidden');
            } else {
                element.classList.add('hidden');
            }
        }
    </script>
    @endpush
</x-app-layout>