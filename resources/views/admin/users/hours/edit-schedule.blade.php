@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Werkschema Bewerken</h1>
                    <p class="mt-1 text-sm text-gray-600">Stel werkdagen en standaard uren in voor {{ $user->name }}</p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('admin.users.hours.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Terug naar overzicht
                    </a>
                </div>
            </div>
        </div>

        <!-- User Info -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex items-center">
                <div class="h-12 w-12 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold text-lg">
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                </div>
                <div class="ml-4">
                    <h3 class="text-lg font-medium text-gray-900">{{ $user->name }}</h3>
                    <p class="text-sm text-gray-500">{{ $user->email }} • {{ $user->role }}</p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('admin.users.hours.update-schedule', $user) }}">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <!-- Working Days -->
                <div class="mb-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Werkdagen</h2>
                    <p class="text-sm text-gray-600 mb-4">Selecteer de dagen waarop deze gebruiker normaal werkt</p>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                        @php
                            $days = [
                                'monday' => 'Maandag',
                                'tuesday' => 'Dinsdag',
                                'wednesday' => 'Woensdag',
                                'thursday' => 'Donderdag',
                                'friday' => 'Vrijdag',
                                'saturday' => 'Zaterdag',
                                'sunday' => 'Zondag'
                            ];
                        @endphp
                        
                        @foreach($days as $key => $label)
                            <label class="relative flex items-start">
                                <div class="flex items-center h-5">
                                    <input type="checkbox" 
                                           name="{{ $key }}" 
                                           value="1"
                                           {{ old($key, $workSchedule->$key) ? 'checked' : '' }}
                                           class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                                </div>
                                <div class="ml-3 text-sm">
                                    <span class="font-medium text-gray-700">{{ $label }}</span>
                                </div>
                            </label>
                        @endforeach
                    </div>
                </div>

                <!-- Hours Configuration -->
                <div class="border-t pt-8">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Uren Configuratie</h2>
                    
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div>
                            <label for="hours_per_day" class="block text-sm font-medium text-gray-700 mb-1">
                                Uren per dag
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <input type="number" 
                                       name="hours_per_day" 
                                       id="hours_per_day"
                                       value="{{ old('hours_per_day', $workSchedule->hours_per_day) }}"
                                       step="0.5"
                                       min="0"
                                       max="24"
                                       class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md @error('hours_per_day') border-red-300 @enderror"
                                       required>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">uur</span>
                                </div>
                            </div>
                            @error('hours_per_day')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Uren per week
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <input type="text" 
                                       id="hours_per_week"
                                       class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md bg-gray-50"
                                       readonly>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">uur</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Automatisch berekend</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Uren per maand
                            </label>
                            <div class="relative rounded-md shadow-sm">
                                <input type="text" 
                                       id="hours_per_month"
                                       class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md bg-gray-50"
                                       readonly>
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">uur</span>
                                </div>
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Gemiddeld (week × 4.33)</p>
                        </div>
                    </div>
                </div>

                <!-- Common Schedules -->
                <div class="mt-8 border-t pt-8">
                    <h3 class="text-sm font-medium text-gray-700 mb-3">Veelgebruikte schema's</h3>
                    <div class="flex flex-wrap gap-2">
                        <button type="button" 
                                onclick="setSchedule('fulltime')"
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-calendar-check mr-1"></i>
                            Fulltime (40u)
                        </button>
                        <button type="button" 
                                onclick="setSchedule('parttime32')"
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-calendar-day mr-1"></i>
                            4 dagen (32u)
                        </button>
                        <button type="button" 
                                onclick="setSchedule('parttime24')"
                                class="inline-flex items-center px-3 py-1.5 border border-gray-300 text-xs font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                            <i class="fas fa-calendar-minus mr-1"></i>
                            3 dagen (24u)
                        </button>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex items-center justify-end space-x-3 border-t pt-8">
                    <a href="{{ route('admin.users.hours.index') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Annuleren
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        <i class="fas fa-save mr-2"></i>
                        Werkschema Opslaan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Calculate hours automatically
    function calculateHours() {
        const hoursPerDay = parseFloat(document.getElementById('hours_per_day').value) || 0;
        let workingDays = 0;
        
        // Count checked days
        @foreach($days as $key => $label)
            if (document.querySelector('input[name="{{ $key }}"]').checked) {
                workingDays++;
            }
        @endforeach
        
        const hoursPerWeek = hoursPerDay * workingDays;
        const hoursPerMonth = hoursPerWeek * 4.33;
        
        document.getElementById('hours_per_week').value = hoursPerWeek.toFixed(1);
        document.getElementById('hours_per_month').value = hoursPerMonth.toFixed(1);
    }

    // Set predefined schedules
    function setSchedule(type) {
        // Reset all days
        @foreach($days as $key => $label)
            document.querySelector('input[name="{{ $key }}"]').checked = false;
        @endforeach
        
        switch(type) {
            case 'fulltime':
                document.querySelector('input[name="monday"]').checked = true;
                document.querySelector('input[name="tuesday"]').checked = true;
                document.querySelector('input[name="wednesday"]').checked = true;
                document.querySelector('input[name="thursday"]').checked = true;
                document.querySelector('input[name="friday"]').checked = true;
                document.getElementById('hours_per_day').value = 8;
                break;
            case 'parttime32':
                document.querySelector('input[name="monday"]').checked = true;
                document.querySelector('input[name="tuesday"]').checked = true;
                document.querySelector('input[name="wednesday"]').checked = true;
                document.querySelector('input[name="thursday"]').checked = true;
                document.getElementById('hours_per_day').value = 8;
                break;
            case 'parttime24':
                document.querySelector('input[name="monday"]').checked = true;
                document.querySelector('input[name="tuesday"]').checked = true;
                document.querySelector('input[name="wednesday"]').checked = true;
                document.getElementById('hours_per_day').value = 8;
                break;
        }
        
        calculateHours();
    }

    // Add event listeners
    document.getElementById('hours_per_day').addEventListener('input', calculateHours);
    @foreach($days as $key => $label)
        document.querySelector('input[name="{{ $key }}"]').addEventListener('change', calculateHours);
    @endforeach

    // Calculate on page load
    calculateHours();
</script>
@endpush
@endsection