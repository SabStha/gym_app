<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;


use Illuminate\Support\Facades\Auth;

class HubController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        
        // Check for active workout
        $activeWorkout = $user->workouts()
            ->where('status', 'in_progress')
            ->latest('started_at')
            ->first();

        // Check if user has seen onboarding
        // ... (existing logic if any)

        return view('hub', [
            'activeWorkoutId' => $activeWorkout ? $activeWorkout->id : null,
            'hide_bottom_nav' => true
        ]);
    }
}
