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

        // Next Session Logic
        $nextSession = null;
        $upcomingSessions = collect();

        if ($activeRoutine) {
            $days = $activeRoutine->routineDays()->orderBy('order_index')->get();
            
            if ($days->isNotEmpty()) {
                // Find last completed workout for this routine
                $lastRoutineWorkout = \App\Models\Workout::where('user_id', auth()->id())
                    ->where('status', 'completed')
                    ->whereHas('routineDay', function($q) use ($activeRoutine) {
                        $q->where('routine_id', $activeRoutine->id);
                    })
                    ->latest('finished_at')
                    ->first();

                $nextIndex = 0;
                if ($lastRoutineWorkout && $lastRoutineWorkout->routineDay) {
                    // Find index of last workout's day
                    $lastDayIndex = $days->search(function($day) use ($lastRoutineWorkout) {
                        return $day->id === $lastRoutineWorkout->routine_day_id;
                    });

                    if ($lastDayIndex !== false) {
                        $nextIndex = ($lastDayIndex + 1) % $days->count();
                    }
                }

                $nextSession = $days[$nextIndex];

                // Upcoming (Next 3)
                for ($i = 1; $i <= 3; $i++) {
                    $upcomingSessions->push($days[($nextIndex + $i) % $days->count()]);
                }
            }
        }

        return view('dashboard', compact(
            'activeRoutine', 
            'currentWorkout', 
            'lastWorkout', 
            'workoutsThisWeek', 
            'totalVolume', 
            'topExercise',
            'nextSession',
            'upcomingSessions'
        ));
    }
}
