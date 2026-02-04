<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use App\Models\RoutineDay;
use App\Models\WorkoutExercise;
use App\Models\WorkoutSet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class WorkoutController extends Controller
{
    /**
     * Show the page to start a workout (Select Day).
     */
    public function create()
    {
        $activeRoutine = auth()->user()->routines()->where('is_active', true)->with('routineDays')->first();

        if (!$activeRoutine) {
            return redirect()->route('routines.index')->with('error', 'Please activate a routine first.');
        }

        return view('workouts.start', compact('activeRoutine'));
    }

    /**
     * Start the workout (Create initial DB records).
     */
    public function start(Request $request)
    {
        $validated = $request->validate([
            'routine_day_id' => 'required|exists:routine_days,id',
        ]);

        $routineDay = RoutineDay::with('dayExercises.exercise')->findOrFail($validated['routine_day_id']);

        // Verify ownership via routine
        if ($routineDay->routine->user_id !== auth()->id()) {
            abort(403);
        }

        $workout = DB::transaction(function () use ($routineDay) {
            // Create Workout
            $workout = Workout::create([
                'user_id' => auth()->id(),
                'routine_day_id' => $routineDay->id,
                'workout_date' => now(),
                'started_at' => now(), // Auto-start
                'status' => 'in_progress',
            ]);

            // Create Workout Exercises from Template
            foreach ($routineDay->dayExercises as $dayExercise) {
                WorkoutExercise::create([
                    'workout_id' => $workout->id,
                    'exercise_id' => $dayExercise->exercise_id,
                    'order_index' => $dayExercise->order_index,
                    'difficulty' => 'ok', 
                ]);
            }

            return $workout;
        });

        return redirect()->route('workouts.show', $workout);
    }

    /**
     * Show the active workout session (The Logger).
     */
    public function show(Workout $workout)
    {
        $this->authorize('view', $workout);

        // If finished, show summary
        if ($workout->status !== 'in_progress') {
            return view('workouts.summary', compact('workout'));
        }

        // Load necessary relations
        $workout->load([
            'workoutExercises.exercise',
            'workoutExercises.workoutSets',
            'routineDay.dayExercises'
        ]);

        return view('workouts.session', compact('workout'));
    }

    /**
     * Finish the workout and save all sets.
     */
    public function finish(Request $request, Workout $workout)
    {
        $this->authorize('update', $workout);

        // Loose validation for the dynamic form
        $validated = $request->validate([
            'note' => 'nullable|string',
            'exercises' => 'array',
            'sets' => 'array',
        ]);

        DB::transaction(function () use ($request, $workout) {
            // Calculate final duration automatically
            $finishedAt = now();
            // Fallback for duration_min column if needed for simpler queries later
            $durationMin = $workout->started_at ? $workout->started_at->diffInMinutes($finishedAt) : null;

            // Update Workout Details
            $workout->update([
                'status' => 'completed',
                'finished_at' => $finishedAt,
                'duration_min' => $durationMin,
                'note' => $request->note,
            ]);

            // Process Exercises (Difficulty)
            if ($request->has('exercises')) {
                foreach ($request->exercises as $weId => $data) {
                    $workoutExercise = WorkoutExercise::where('workout_id', $workout->id)
                        ->where('id', $weId)
                        ->first();
                    
                    if ($workoutExercise && isset($data['difficulty'])) {
                        $workoutExercise->update(['difficulty' => $data['difficulty']]);
                    }
                }
            }

            // Sync Sets
            // Strategy: Delete existing for this workout, re-create from input
            $weIds = $workout->workoutExercises()->pluck('id');
            WorkoutSet::whereIn('workout_exercise_id', $weIds)->delete();

            if ($request->has('sets')) {
                foreach ($request->sets as $weId => $rows) {
                    // Verify WE belongs to workout
                    if (!$weIds->contains($weId)) continue;

                    $setCount = 1;
                    foreach ($rows as $row) {
                        // Only save if meaningful data is present
                        if ((isset($row['weight']) && $row['weight'] !== '') || (isset($row['reps']) && $row['reps'] !== '')) {
                             WorkoutSet::create([
                                'workout_exercise_id' => $weId,
                                'set_number' => $setCount++,
                                'weight_kg' => $row['weight'] ?? 0, // Handle optional/empty
                                'reps' => $row['reps'] ?? 0,
                            ]);
                        }
                    }
                }
            }
        });

        // Redirect to summary view using the view directly or route if we made a dedicated route (we did not ask for a dedicated GET summary route, just finish redirects to view? 
        // User prompt: "Redirect to workouts.summary view" -> usually implies returning view or redirecting to a helper. 
        // Since 'show' handles finished workouts by showing summary, redirecting to 'show' is best practice.
        return redirect()->route('workouts.show', $workout);
    }
}
