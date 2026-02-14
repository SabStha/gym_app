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
    public function store(Request $request)
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

        // Fetch History for Initial Values
        $history = [];
        $userId = auth()->id();
        $exerciseIds = $workout->workoutExercises->pluck('exercise_id')->unique();

        if ($exerciseIds->isNotEmpty()) {
            // Optimization: Fetch all latest finished WorkoutExercises for these exercises in one query if possible,
            // or simple loop for readability since exercise count is low (usually 5-8).
            // A precise "latest per group" query is complex in Eloquent. Loop is fine here.
            
            foreach ($exerciseIds as $exerciseId) {
                $lastWe = WorkoutExercise::whereHas('workout', function ($q) use ($userId) {
                        $q->where('user_id', $userId)
                          ->where('status', 'completed');
                    })
                    ->where('exercise_id', $exerciseId)
                    ->latest()
                    ->with('workoutSets')
                    ->first();

                if ($lastWe) {
                    $sets = [];
                    foreach ($lastWe->workoutSets as $s) {
                        $sets[$s->set_number] = [
                            'weight' => $s->weight_kg,
                            'reps' => $s->reps,
                        ];
                    }
                    $history[$exerciseId] = $sets;
                }
            }
        }

        return view('workouts.session', compact('workout', 'history'));
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
            // CRITICAL FIX: Do NOT wipe existing sets. Rely on AJAX saves or update if data provided.
            $weIds = $workout->workoutExercises()->pluck('id');
            // Old Logic Removed: WorkoutSet::whereIn('workout_exercise_id', $weIds)->delete();

            if ($request->has('sets')) {
                foreach ($request->sets as $weId => $rows) {
                    // Verify WE belongs to workout
                    if (!$weIds->contains($weId)) continue;
                    
                    // Note: The form submission relies on the order of rows to determine set_number.
                    // If using AJAX, this might be redundant or supplementary.
                    $setCount = 1;
                    foreach ($rows as $row) {
                        // Only save if meaningful data is present
                        if ((isset($row['weight']) && $row['weight'] !== '') || (isset($row['reps']) && $row['reps'] !== '')) {
                             WorkoutSet::updateOrCreate(
                                [
                                    'workout_exercise_id' => $weId,
                                    'set_number' => $setCount,
                                ],
                                [
                                    'weight_kg' => $row['weight'] ?? 0,
                                    'reps' => $row['reps'] ?? 0,
                                ]
                            );
                        }
                        $setCount++;
                    }
                }
            }
        });
        
        // Log for debugging
        \Illuminate\Support\Facades\Log::info("Workout {$workout->id} finished. Sets preserved.");

        // Redirect to summary view using the view directly or route if we made a dedicated route (we did not ask for a dedicated GET summary route, just finish redirects to view? 
        // User prompt: "Redirect to workouts.summary view" -> usually implies returning view or redirecting to a helper. 
        // Since 'show' handles finished workouts by showing summary, redirecting to 'show' is best practice.
        return redirect()->route('workouts.show', $workout);
    }

    /**
     * Reorder exercises in a workout.
     */
    public function reorder(Request $request, Workout $workout)
    {
        $this->authorize('update', $workout);

        $validated = $request->validate([
            'order' => 'required|array',
            'order.*' => 'exists:workout_exercises,id',
        ]);

        foreach ($validated['order'] as $index => $id) {
            // Verify ID belongs to workout
            WorkoutExercise::where('id', $id)
                ->where('workout_id', $workout->id)
                ->update(['order_index' => $index]);
        }

        return response()->json(['status' => 'success']);
    }

    /**
     * Update status of a workout exercise.
     */
    public function updateStatus(Request $request, Workout $workout, WorkoutExercise $workoutExercise)
    {
        $this->authorize('update', $workout);

        if ($workoutExercise->workout_id !== $workout->id) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,completed,skipped',
        ]);

        $workoutExercise->update(['status' => $validated['status']]);

        return response()->json(['status' => 'success']);
    }
    /**
     * Get the current queue.
     */
    public function queue(Workout $workout)
    {
        $this->authorize('view', $workout);
        
        $exercises = $workout->workoutExercises()
            ->with(['exercise', 'workoutSets'])
            ->orderBy('order_index') // Use the explicit order
            ->get();
            
        return response()->json($exercises);
    }

    /**
     * Set the current active exercise (Go To).
     */
    public function setCurrentExercise(Request $request, Workout $workout)
    {
        $this->authorize('update', $workout);
        
        $validated = $request->validate([
            'workout_exercise_id' => 'required|exists:workout_exercises,id',
        ]);
        
        // Verify belongs to workout
        $we = WorkoutExercise::where('id', $validated['workout_exercise_id'])
            ->where('workout_id', $workout->id)
            ->firstOrFail();
            
        $workout->update(['current_workout_exercise_id' => $we->id]);
        
        return response()->json(['status' => 'success']);
    }

    /**
     * Skip current exercise and move to next.
     */
    public function skipExercise(Request $request, Workout $workout)
    {
        $this->authorize('update', $workout);
        
        $validated = $request->validate([
            'workout_exercise_id' => 'required|exists:workout_exercises,id',
        ]);
        
        $we = WorkoutExercise::where('id', $validated['workout_exercise_id'])
            ->where('workout_id', $workout->id)
            ->firstOrFail();
            
        // 1. Mark Skipped
        $we->update(['status' => 'skipped']);
        
        // 2. Move to end of queue
        $maxOrder = $workout->workoutExercises()->max('order_index');
        $we->update(['order_index' => $maxOrder + 1]);
        
        // 3. Find Next Pending
        $next = $workout->workoutExercises()
            ->where('status', '!=', 'skipped')
            ->where('status', '!=', 'completed')
            ->orderBy('order_index')
            ->first();
            
        if ($next) {
            $workout->update(['current_workout_exercise_id' => $next->id]);
        }
        
        return response()->json([
            'status' => 'success', 
            'next_id' => $next ? $next->id : null
        ]);
    }
}
