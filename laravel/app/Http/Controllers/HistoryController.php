<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use Illuminate\Http\Request;

class HistoryController extends Controller
{
    /**
     * Display a listing of completed workouts.
     */
    public function index()
    {
        $workouts = auth()->user()->workouts()
            ->where('status', 'completed')
            ->orderBy('workout_date', 'desc')
            ->with('routineDay')
            ->withCount('workoutExercises')
            ->paginate(10);

        return view('history.index', compact('workouts'));
    }

    /**
     * Display the specified workout details.
     */
    public function show(Workout $workout)
    {
        // Ensure workout belongs to auth user
        if ($workout->user_id !== auth()->id()) {
            abort(403);
        }

        // Ensure it is completed
        if ($workout->status !== 'completed') {
            return redirect()->route('workouts.show', $workout);
        }

        $workout->load(['workoutExercises.exercise', 'workoutExercises.workoutSets']);

        return view('history.show', compact('workout'));
    }
}
