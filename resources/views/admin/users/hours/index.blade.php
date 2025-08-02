@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-gray-50 py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Urenbeheer</h1>
                    <p class="mt-1 text-sm text-gray-600">Beheer werkschema's en maandelijkse uren</p>
                </div>
                <div class="mt-4 sm:mt-0 flex space-x-3">
                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Terug naar Gebruikers
                    </a>
                    <a href="{{ route('admin.users.hours.export', ['year' => $year, 'month' => $month]) }}" class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                        <i class="fas fa-download mr-2"></i>
                        Export CSV
                    </a>
                </div>
            </div>
        </div>

        <!-- Month Selector -->
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6 mb-6">
            <form method="GET" action="{{ route('admin.users.hours.index') }}" class="flex items-center space-x-4">
                <div>
                    <label for="month" class="block text-sm font-medium text-gray-700 mb-1">Maand</label>
                    <select name="month" id="month" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @php
                            $months = [
                                1 => 'Januari', 2 => 'Februari', 3 => 'Maart', 4 => 'April',
                                5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Augustus',
                                9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'December'
                            ];
                        @endphp
                        @foreach($months as $num => $name)
                            <option value="{{ $num }}" {{ $month == $num ? 'selected' : '' }}>{{ $name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="year" class="block text-sm font-medium text-gray-700 mb-1">Jaar</label>
                    <select name="year" id="year" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                        @for($y = date('Y') - 2; $y <= date('Y') + 1; $y++)
                            <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                        @endfor
                    </select>
                </div>
                <div class="pt-6">
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                        <i class="fas fa-calendar-check mr-2"></i>
                        Toon Maand
                    </button>
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
                                Werkdagen
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Contract
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Declarabel
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Niet-Declarabel
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Afwezig
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Productiviteit
                            </th>
                            <th scope="col" class="px-6 py-3 text-center text-xs font-medium text-gray-500 uppercase tracking-wider">
                                Declarabiliteit
                            </th>
                            <th scope="col" class="relative px-6 py-3">
                                <span class="sr-only">Acties</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($users as $user)
                            @php
                                $schedule = $user->workSchedule;
                                $hours = $user->monthlyHours->first();
                            @endphp
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full bg-indigo-500 flex items-center justify-center text-white font-semibold">
                                                {{ strtoupper(substr($user->name, 0, 1)) }}
                                            </div>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ $user->name }}</div>
                                            <div class="text-sm text-gray-500">{{ $user->role }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @if($schedule)
                                        <div class="flex space-x-1">
                                            @foreach(['M' => $schedule->monday, 'D' => $schedule->tuesday, 'W' => $schedule->wednesday, 'D' => $schedule->thursday, 'V' => $schedule->friday, 'Z' => $schedule->saturday, 'Z' => $schedule->sunday] as $day => $works)
                                                <span class="inline-flex items-center justify-center w-6 h-6 rounded-full text-xs font-medium {{ $works ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-400' }}">
                                                    {{ $day }}
                                                </span>
                                            @endforeach
                                        </div>
                                        <div class="text-xs text-gray-500 mt-1">{{ $schedule->hours_per_day }}u/dag</div>
                                    @else
                                        <span class="text-sm text-gray-500">Niet ingesteld</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="text-sm font-medium text-gray-900">{{ $hours->contracted_hours ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="text-sm text-green-600">{{ $hours->billable_hours ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <span class="text-sm text-gray-600">{{ $hours->non_billable_hours ?? '-' }}</span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($hours)
                                        <div class="text-sm">
                                            <span class="text-blue-600">{{ $hours->vacation_hours }}v</span> /
                                            <span class="text-red-600">{{ $hours->sick_hours }}z</span>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($hours && $hours->contracted_hours > 0)
                                        <div class="flex items-center justify-center">
                                            <span class="text-sm font-medium {{ $hours->productivity_percentage >= 90 ? 'text-green-600' : ($hours->productivity_percentage >= 75 ? 'text-yellow-600' : 'text-red-600') }}">
                                                {{ $hours->productivity_percentage }}%
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    @if($hours && $hours->total_worked_hours > 0)
                                        <div class="flex items-center justify-center">
                                            <span class="text-sm font-medium {{ $hours->billability_percentage >= 80 ? 'text-green-600' : ($hours->billability_percentage >= 60 ? 'text-yellow-600' : 'text-red-600') }}">
                                                {{ $hours->billability_percentage }}%
                                            </span>
                                        </div>
                                    @else
                                        <span class="text-sm text-gray-500">-</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="{{ route('admin.users.hours.edit-schedule', $user) }}" 
                                           class="text-gray-600 hover:text-gray-900"
                                           title="Werkschema bewerken">
                                            <i class="fas fa-calendar-alt"></i>
                                        </a>
                                        <a href="{{ route('admin.users.hours.edit-monthly', [$user, $year, $month]) }}" 
                                           class="text-indigo-600 hover:text-indigo-900"
                                           title="Maanduren bewerken">
                                            <i class="fas fa-clock"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Summary -->
        <div class="mt-6 grid grid-cols-1 md:grid-cols-4 gap-4">
            @php
                $totalContracted = $users->sum(fn($u) => $u->monthlyHours->first()->contracted_hours ?? 0);
                $totalBillable = $users->sum(fn($u) => $u->monthlyHours->first()->billable_hours ?? 0);
                $totalNonBillable = $users->sum(fn($u) => $u->monthlyHours->first()->non_billable_hours ?? 0);
                $totalWorked = $totalBillable + $totalNonBillable;
            @endphp
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h3 class="text-sm font-medium text-gray-500">Totaal Contract Uren</h3>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($totalContracted, 2) }}</p>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h3 class="text-sm font-medium text-gray-500">Totaal Gewerkt</h3>
                <p class="mt-2 text-2xl font-semibold text-gray-900">{{ number_format($totalWorked, 2) }}</p>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h3 class="text-sm font-medium text-gray-500">Declarabel</h3>
                <p class="mt-2 text-2xl font-semibold text-green-600">{{ number_format($totalBillable, 2) }}</p>
            </div>
            
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
                <h3 class="text-sm font-medium text-gray-500">Gemiddelde Declarabiliteit</h3>
                <p class="mt-2 text-2xl font-semibold {{ $totalWorked > 0 && ($totalBillable / $totalWorked) >= 0.8 ? 'text-green-600' : 'text-yellow-600' }}">
                    {{ $totalWorked > 0 ? number_format(($totalBillable / $totalWorked) * 100, 1) : 0 }}%
                </p>
            </div>
        </div>
    </div>
</div>
@endsection