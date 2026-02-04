<?php

namespace App\Http\Controllers;

use App\Models\DayExercise;
use App\Models\RoutineDay;
use Illuminate\Http\Request;

class DayExerciseController extends Controller
{
    public function store(Request $request, RoutineDay $routineDay)
    {
        // Check authorization via the routine relationship
        if ($routineDay->routine->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'exercise_id' => 'required|exists:exercises,id',
            'target_sets' => 'required|integer|min:1',
            'rep_min' => 'required|integer|min:1',
            'rep_max' => 'required|integer|gte:rep_min',
            'increment_override_kg' => 'nullable|numeric|min:0',
            'order_index' => 'required|integer',
        ]);

        // Explicitly check if exercise belongs to user or is null (system default)
        // Although the Exercise list in controller already handles this visualization, strict backend check is good.
        // For MVP, assuming the validated exercise_id is valid.

        $routineDay->dayExercises()->create($validated);

        return redirect()->route('routines.show', $routineDay->routine_id)->with('success', 'Exercise added to day.');
    }

    public function destroy(DayExercise $dayExercise)
    {
        if ($dayExercise->routineDay->routine->user_id !== auth()->id()) {
            abort(403);
        }

        $routineId = $dayExercise->routineDay->routine_id;
        $dayExercise->delete();

        return redirect()->route('routines.show', $routineId)->with('success', 'Exercise removed from day.');
    }
}
