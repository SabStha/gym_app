<?php

namespace App\Http\Controllers;

use App\Models\Routine;
use App\Models\RoutineDay;
use Illuminate\Http\Request;

class RoutineDayController extends Controller
{
    public function show(Routine $routine, RoutineDay $day)
    {
        if ($routine->user_id !== auth()->id()) {
            abort(403);
        }
        
        // Ensure day belongs to routine
        if ($day->routine_id !== $routine->id) {
            abort(404);
        }

        $routine->load(['routineDays' => function($q) {
            $q->orderBy('order_index');
        }]);

        // Load exercises for the carousel
        $day->load(['dayExercises.exercise']);

        return view('routines.days.show', compact('routine', 'day'));
    }

    public function store(Request $request, Routine $routine)
    {
        if ($routine->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'day_name' => 'required|string|max:255',
            'order_index' => 'required|integer',
        ]);

        $day = $routine->routineDays()->create($validated);

        if ($request->wantsJson()) {
            return response()->json([
                'success' => true,
                'redirect_url' => route('routine-days.show', [$routine, $day])
            ]);
        }

        return redirect()->route('routines.show', $routine)->with('success', 'Day added successfully.');
    }

    public function update(Request $request, RoutineDay $routineDay)
    {
        if ($routineDay->routine->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'day_name' => 'required|string|max:255',
        ]);

        $routineDay->update($validated);

        if ($request->wantsJson()) {
            return response()->json(['success' => true]);
        }

        return redirect()->back()->with('success', 'Workout updated.');
    }

    public function destroy(RoutineDay $routineDay)
    {
        if ($routineDay->routine->user_id !== auth()->id()) {
            abort(403);
        }

        $routine = $routineDay->routine;
        $routineDay->delete();

        return redirect()->route('routines.show', $routine)->with('success', 'Day removed successfully.');
    }
}
