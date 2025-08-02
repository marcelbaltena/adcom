<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserWorkSchedule;
use App\Models\UserMonthlyHours;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserHoursController extends Controller
{
    /**
     * Check if user is admin or beheerder
     */
    private function checkAccess()
    {
        if (!auth()->check() || !in_array(auth()->user()->role, ['admin', 'beheerder'])) {
            abort(403, 'Geen toegang - alleen voor administrators en beheerders');
        }
    }

    /**
     * Display hours overview
     */
    public function index(Request $request)
    {
        $this->checkAccess();
        
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('n'));
        
        $users = User::with(['workSchedule', 'monthlyHours' => function($query) use ($year, $month) {
            $query->where('year', $year)->where('month', $month);
        }])->orderBy('name')->get();
        
        return view('admin.users.hours.index', compact('users', 'year', 'month'));
    }

    /**
     * Show work schedule form
     */
    public function editSchedule(User $user)
    {
        $this->checkAccess();
        
        $workSchedule = $user->getOrCreateWorkSchedule();
        
        return view('admin.users.hours.edit-schedule', compact('user', 'workSchedule'));
    }

    /**
     * Update work schedule
     */
    public function updateSchedule(Request $request, User $user)
    {
        $this->checkAccess();
        
        $validated = $request->validate([
            'monday' => 'boolean',
            'tuesday' => 'boolean',
            'wednesday' => 'boolean',
            'thursday' => 'boolean',
            'friday' => 'boolean',
            'saturday' => 'boolean',
            'sunday' => 'boolean',
            'hours_per_day' => 'required|numeric|min:0|max:24',
        ]);
        
        // Bereken werkdagen
        $workDays = 0;
        foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day) {
            if ($request->input($day)) $workDays++;
            $validated[$day] = $request->input($day, false);
        }
        
        // Bereken week en maand uren
        $validated['hours_per_week'] = $workDays * $validated['hours_per_day'];
        $validated['hours_per_month'] = $validated['hours_per_week'] * 4.33; // Gemiddeld aantal weken per maand
        
        $workSchedule = $user->workSchedule ?? new UserWorkSchedule(['user_id' => $user->id]);
        $workSchedule->fill($validated);
        $workSchedule->save();
        
        return redirect()->route('admin.users.hours.index')
            ->with('success', 'Werkschema succesvol bijgewerkt');
    }

    /**
     * Show monthly hours form
     */
    public function editMonthly(User $user, $year, $month)
    {
        $this->checkAccess();
        
        $monthlyHours = $user->getMonthlyHours($year, $month);
        $workSchedule = $user->getOrCreateWorkSchedule();
        
        // Bereken standaard contracturen voor deze maand
        $defaultHours = $workSchedule->calculateMonthlyHours($year, $month);
        
        if (!$monthlyHours) {
            $monthlyHours = new UserMonthlyHours([
                'user_id' => $user->id,
                'year' => $year,
                'month' => $month,
                'contracted_hours' => $defaultHours,
            ]);
        }
        
        return view('admin.users.hours.edit-monthly', compact('user', 'monthlyHours', 'year', 'month', 'workSchedule'));
    }

    /**
     * Update monthly hours
     */
    public function updateMonthly(Request $request, User $user, $year, $month)
    {
        $this->checkAccess();
        
        $validated = $request->validate([
            'contracted_hours' => 'required|numeric|min:0|max:999',
            'billable_hours' => 'nullable|numeric|min:0|max:999',
            'non_billable_hours' => 'nullable|numeric|min:0|max:999',
            'vacation_hours' => 'required|numeric|min:0|max:999',
            'sick_hours' => 'required|numeric|min:0|max:999',
            'notes' => 'nullable|string|max:1000',
        ]);
        
        $monthlyHours = UserMonthlyHours::updateOrCreate(
            [
                'user_id' => $user->id,
                'year' => $year,
                'month' => $month,
            ],
            $validated
        );
        
        return redirect()->route('admin.users.hours.index', ['year' => $year, 'month' => $month])
            ->with('success', 'Maandelijkse uren succesvol bijgewerkt');
    }

    /**
     * Export hours to CSV
     */
    public function export(Request $request)
    {
        $this->checkAccess();
        
        $year = $request->input('year', date('Y'));
        $month = $request->input('month', date('n'));
        
        $users = User::with(['workSchedule', 'monthlyHours' => function($query) use ($year, $month) {
            $query->where('year', $year)->where('month', $month);
        }])->orderBy('name')->get();
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="uren-' . $year . '-' . str_pad($month, 2, '0', STR_PAD_LEFT) . '.csv"',
        ];
        
        $callback = function() use ($users, $year, $month) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'Naam',
                'Email',
                'Rol',
                'Contract Uren',
                'Declarabele Uren',
                'Niet-Declarabele Uren',
                'Vakantie Uren',
                'Ziekte Uren',
                'Totaal Gewerkt',
                'Productiviteit %',
                'Declarabiliteit %'
            ]);
            
            // Data
            foreach ($users as $user) {
                $hours = $user->monthlyHours->first();
                
                fputcsv($file, [
                    $user->name,
                    $user->email,
                    $user->role,
                    $hours->contracted_hours ?? 0,
                    $hours->billable_hours ?? 0,
                    $hours->non_billable_hours ?? 0,
                    $hours->vacation_hours ?? 0,
                    $hours->sick_hours ?? 0,
                    $hours->total_worked_hours ?? 0,
                    $hours->productivity_percentage ?? 0,
                    $hours->billability_percentage ?? 0,
                ]);
            }
            
            fclose($file);
        };
        
        return response()->stream($callback, 200, $headers);
    }
}