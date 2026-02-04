<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Routine;
use App\Models\Workout;

class DashboardController extends Controller
{
    /**
     * Show the application dashboard.
     */
    public function index()
    {
        $activeRoutine = auth()->user()->routines()
            ->where('is_active', true)
            ->first();

        // Check for in-progress workout
        $currentWorkout = auth()->user()->workouts()
            ->where('status', 'in_progress')
            ->latest('started_at')
            ->first();

        // Get the last completed workout
        $lastWorkout = auth()->user()->workouts()
            ->where('status', 'completed')
            ->latest('workout_date')
            ->first();

        // Stats for cards (This Week)
        $startOfWeek = now()->startOfWeek();
        $endOfWeek = now()->endOfWeek();

        $workoutsThisWeek = auth()->user()->workouts()
            ->where('status', 'completed')
            ->whereBetween('workout_date', [$startOfWeek, $endOfWeek])
            ->count();

        // Volume: sum(weight * reps) for completed workouts this week
        $totalVolume = \App\Models\WorkoutSet::whereHas('workoutExercise.workout', function ($q) use ($startOfWeek, $endOfWeek) {
                $q->where('user_id', auth()->id())
                  ->where('status', 'completed')
                  ->whereBetween('workout_date', [$startOfWeek, $endOfWeek]);
            })
            ->sum(\Illuminate\Support\Facades\DB::raw('weight_kg * reps'));

        // Top Exercise: Most frequently performed this week
        $topExerciseStat = \App\Models\WorkoutExercise::select('exercise_id', \Illuminate\Support\Facades\DB::raw('count(*) as count'))
            ->whereHas('workout', function ($q) use ($startOfWeek, $endOfWeek) {
                $q->where('user_id', auth()->id())
                  ->where('status', 'completed')
                  ->whereBetween('workout_date', [$startOfWeek, $endOfWeek]);
            })
            ->groupBy('exercise_id')
            ->orderByDesc('count')
            ->with('exercise')
            ->first();

        $topExercise = $topExerciseStat ? $topExerciseStat->exercise->name : 'N/A';

        return view('dashboard', compact('activeRoutine', 'currentWorkout', 'lastWorkout', 'workoutsThisWeek', 'totalVolume', 'topExercise'));
    }
}
