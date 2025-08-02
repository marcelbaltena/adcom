<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Project;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            // Simple dashboard data
            $totalProjects = Project::count();
            $recentProjects = Project::latest()->take(5)->get();
            
            return view('dashboard', compact('totalProjects', 'recentProjects'));
        } catch (\Exception $e) {
            return view('dashboard', [
                'totalProjects' => 0,
                'recentProjects' => collect(),
                'error' => 'Unable to load dashboard data.'
            ]);
        }
    }
}