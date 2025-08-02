@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Maandelijkse Uren</h1>
                    <p class="mt-1 text-sm text-gray-600">
                        Registreer uren voor {{ $user->name }} - 
                        @php
                            $months = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maart', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Augustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'December'
                            ];
                        @endphp
                        {{ $months[$month] }} {{ $year }}
                    </p>
                </div>
                <div class="mt-4 sm:mt-0">
                    <a href="{{ route('admin.users.hours.index', ['year' => $year, 'month' => $month]) }}" 
                       class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Terug naar overzicht
                    </a>
                </div>
            </div>
        </div>

        <!-- User & Schedule Info -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            <!-- User Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Gebruiker</h3>
                <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-gray-900">{{ $user->name }}</p>
                        <p class="text-sm text-gray-500">{{ $user->role }}</p>
                    </div>
                </div>
            </div>

            <!-- Schedule Info -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <h3 class="text-sm font-medium text-gray-500 mb-3">Werkschema</h3>
                <div class="text-sm">
                    <p class="text-gray-900">
                        <span class="font-medium">Werkdagen:</span> 
                        {{ implode(', ', $workSchedule->working_days) }}
                    </p>
                    <p class="text-gray-900 mt-1">
                        <span class="font-medium">Verwachte uren deze maand:</span> 
                        {{ number_format($workSchedule->calculateMonthlyHours($year, $month), 1) }} uur
                    </p>
                </div>
            </div>
        </div>

        <!-- Form -->
        <form method="POST" action="{{ route('admin.users.hours.update-monthly', [$user, $year, $month]) }}">
            @csrf
            @method('PUT')

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
                <!-- Hours Input -->
                <div class="space-y-6">
                    <!-- Contract Hours -->
                    <div>
                        <label for="contracted_hours" class="block text-sm font-medium text-gray-700 mb-1">
                            Gecontracteerde uren <span class="text-red-500">*</span>
                        </label>
                        <div class="mt-1 relative rounded-md shadow-sm max-w-xs">
                            <input type="number" 
                                   name="contracted_hours" 
                                   id="contracted_hours"
                                   value="{{ old('contracted_hours', $monthlyHours->contracted_hours ?? $workSchedule->calculateMonthlyHours($year, $month)) }}"
                                   step="0.5"
                                   min="0"
                                   max="999"
                                   class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md @error('contracted_hours') border-red-300 @enderror"
                                   required>
                            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 sm:text-sm">uur</span>
                            </div>
                        </div>
                        @error('contracted_hours')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500">Aantal uren volgens contract voor deze maand</p>
                    </div>

                    <!-- Worked Hours -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Gewerkte Uren</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="billable_hours" class="block text-sm font-medium text-gray-700 mb-1">
                                    Declarabele uren
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="number" 
                                           name="billable_hours" 
                                           id="billable_hours"
                                           value="{{ old('billable_hours', $monthlyHours->billable_hours) }}"
                                           step="0.5"
                                           min="0"
                                           max="999"
                                           class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md @error('billable_hours') border-red-300 @enderror">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">uur</span>
                                    </div>
                                </div>
                                @error('billable_hours')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Uren die gefactureerd kunnen worden</p>
                            </div>

                            <div>
                                <label for="non_billable_hours" class="block text-sm font-medium text-gray-700 mb-1">
                                    Niet-declarabele uren
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="number" 
                                           name="non_billable_hours" 
                                           id="non_billable_hours"
                                           value="{{ old('non_billable_hours', $monthlyHours->non_billable_hours) }}"
                                           step="0.5"
                                           min="0"
                                           max="999"
                                           class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md @error('non_billable_hours') border-red-300 @enderror">
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">uur</span>
                                    </div>
                                </div>
                                @error('non_billable_hours')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                                <p class="mt-1 text-xs text-gray-500">Interne uren, overhead, etc.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Absence Hours -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-medium text-gray-900 mb-4">Afwezigheid</h3>
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="vacation_hours" class="block text-sm font-medium text-gray-700 mb-1">
                                    Vakantie uren <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="number" 
                                           name="vacation_hours" 
                                           id="vacation_hours"
                                           value="{{ old('vacation_hours', $monthlyHours->vacation_hours ?? 0) }}"
                                           step="0.5"
                                           min="0"
                                           max="999"
                                           class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md @error('vacation_hours') border-red-300 @enderror"
                                           required>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">uur</span>
                                    </div>
                                </div>
                                @error('vacation_hours')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="sick_hours" class="block text-sm font-medium text-gray-700 mb-1">
                                    Ziekte uren <span class="text-red-500">*</span>
                                </label>
                                <div class="mt-1 relative rounded-md shadow-sm">
                                    <input type="number" 
                                           name="sick_hours" 
                                           id="sick_hours"
                                           value="{{ old('sick_hours', $monthlyHours->sick_hours ?? 0) }}"
                                           step="0.5"
                                           min="0"
                                           max="999"
                                           class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pr-12 sm:text-sm border-gray-300 rounded-md @error('sick_hours') border-red-300 @enderror"
                                           required>
                                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                        <span class="text-gray-500 sm:text-sm">uur</span>
                                    </div>
                                </div>
                                @error('sick_hours')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Notes -->
                    <div class="border-t pt-6">
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            Opmerkingen
                        </label>
                        <textarea name="notes" 
                                  id="notes"
                                  rows="3"
                                  class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md @error('notes') border-red-300 @enderror"
                                  placeholder="Optionele opmerkingen...">{{ old('notes', $monthlyHours->notes) }}</textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Summary -->
                <div class="mt-8 border-t pt-8">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Samenvatting</h3>
                    
                    <div class="bg-gray-50 rounded-lg p-4">
                        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-3">
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Contract uren:</dt>
                                <dd class="text-sm text-gray-900 font-medium" id="summary-contracted">0</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Totaal gewerkt:</dt>
                                <dd class="text-sm text-gray-900 font-medium" id="summary-worked">0</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Totaal afwezig:</dt>
                                <dd class="text-sm text-gray-900 font-medium" id="summary-absent">0</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Productiviteit:</dt>
                                <dd class="text-sm font-medium" id="summary-productivity">0%</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Declarabiliteit:</dt>
                                <dd class="text-sm font-medium" id="summary-billability">0%</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-sm font-medium text-gray-500">Verschil:</dt>
                                <dd class="text-sm font-medium" id="summary-difference">0</dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-8 flex items-center justify-end space-x-3 border-t pt-8">
                    <a href="{{ route('admin.users.hours.index', ['year' => $year, 'month' => $month]) }}" 
                       class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        Annuleren
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        <i class="fas fa-save mr-2"></i>
                        Uren Opslaan
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    // Calculate summary
    function calculateSummary() {
        const contracted = parseFloat(document.getElementById('contracted_hours').value) || 0;
        const billable = parseFloat(document.getElementById('billable_hours').value) || 0;
        const nonBillable = parseFloat(document.getElementById('non_billable_hours').value) || 0;
        const vacation = parseFloat(document.getElementById('vacation_hours').value) || 0;
        const sick = parseFloat(document.getElementById('sick_hours').value) || 0;
        
        const totalWorked = billable + nonBillable;
        const totalAbsent = vacation + sick;
        const totalAccountedFor = totalWorked + totalAbsent;
        const difference = totalAccountedFor - contracted;
        
        const productivity = contracted > 0 ? (totalWorked / contracted * 100) : 0;
        const billability = totalWorked > 0 ? (billable / totalWorked * 100) : 0;
        
        // Update summary
        document.getElementById('summary-contracted').textContent = contracted.toFixed(1) + ' uur';
        document.getElementById('summary-worked').textContent = totalWorked.toFixed(1) + ' uur';
        document.getElementById('summary-absent').textContent = totalAbsent.toFixed(1) + ' uur';
        
        // Productivity
        const productivityEl = document.getElementById('summary-productivity');
        productivityEl.textContent = productivity.toFixed(1) + '%';
        productivityEl.className = 'text-sm font-medium ' + 
            (productivity >= 90 ? 'text-green-600' : 
             productivity >= 75 ? 'text-yellow-600' : 'text-red-600');
        
        // Billability
        const billabilityEl = document.getElementById('summary-billability');
        billabilityEl.textContent = billability.toFixed(1) + '%';
        billabilityEl.className = 'text-sm font-medium ' + 
            (billability >= 80 ? 'text-green-600' : 
             billability >= 60 ? 'text-yellow-600' : 'text-red-600');
        
        // Difference
        const differenceEl = document.getElementById('summary-difference');
        const sign = difference > 0 ? '+' : '';
        differenceEl.textContent = sign + difference.toFixed(1) + ' uur';
        differenceEl.className = 'text-sm font-medium ' + 
            (Math.abs(difference) < 5 ? 'text-green-600' : 
             Math.abs(difference) < 10 ? 'text-yellow-600' : 'text-red-600');
    }

    // Add event listeners
    document.getElementById('contracted_hours').addEventListener('input', calculateSummary);
    document.getElementById('billable_hours').addEventListener('input', calculateSummary);
    document.getElementById('non_billable_hours').addEventListener('input', calculateSummary);
    document.getElementById('vacation_hours').addEventListener('input', calculateSummary);
    document.getElementById('sick_hours').addEventListener('input', calculateSummary);

    // Calculate on page load
    calculateSummary();
</script>
@endpush
@endsection