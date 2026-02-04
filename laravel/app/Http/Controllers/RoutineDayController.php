<?php

namespace App\Http\Controllers;

use App\Models\Routine;
use App\Models\RoutineDay;
use Illuminate\Http\Request;

class RoutineDayController extends Controller
{
    public function store(Request $request, Routine $routine)
    {
        if ($routine->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'day_name' => 'required|string|max:255',
            'order_index' => 'required|integer',
        ]);

        $routine->routineDays()->create($validated);

        return redirect()->route('routines.show', $routine)->with('success', 'Day added successfully.');
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
