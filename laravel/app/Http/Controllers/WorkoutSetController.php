<?php

namespace App\Http\Controllers;

use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Models\WorkoutSet;
use Illuminate\Http\Request;
use App\Services\ProgressionSuggestionService;

class WorkoutSetController extends Controller
{
    protected $suggestionService;

    public function __construct(ProgressionSuggestionService $suggestionService)
    {
        $this->suggestionService = $suggestionService;
    }

    /**
     * Store (or update) a single set.
     * Expects: workout_exercise_id, set_number, weight_kg, reps.
     * Returns: JSON with next set suggestion.
     */
    public function store(Request $request, Workout $workout)
    {
        // Debug Logging
        \Illuminate\Support\Facades\Log::info("Saving set for Workout {$workout->id}", $request->all());

        // Basic validation
        $validated = $request->validate([
            'workout_exercise_id' => 'required|exists:workout_exercises,id',
            'set_number' => 'required|integer|min:1',
            'weight_kg' => 'required|numeric|min:0',
            'reps' => 'required|integer|min:0',
        ]);

        $we = WorkoutExercise::findOrFail($validated['workout_exercise_id']);

        // Verify ownership
        if ($we->workout_id !== $workout->id || $workout->user_id !== auth()->id()) {
            abort(403);
        }

        // Update or Create the set
        $set = WorkoutSet::updateOrCreate(
            [
                'workout_exercise_id' => $we->id,
                'set_number' => $validated['set_number']
            ],
            [
                'weight_kg' => $validated['weight_kg'],
                'reps' => $validated['reps']
            ]
        );
        
        \Illuminate\Support\Facades\Log::info("Set saved successfully. ID: {$set->id}");

        // Calculate Suggestion for the NEXT set
        $nextSetNumber = $validated['set_number'] + 1;
        $suggestion = $this->suggestionService->getSuggestion($we, $nextSetNumber);

        // Auto-Complete Logic
        // Determine target sets
        $targetSets = 3;
        if ($workout->routineDay) {
            $dayExercise = $workout->routineDay->dayExercises
                ->where('exercise_id', $we->exercise_id)
                ->first();
            if ($dayExercise) {
                $targetSets = $dayExercise->target_sets;
            }
        }

        // If we just saved the last set (or beyond), mark as completed
        $isCompleted = false;
        if ($validated['set_number'] >= $targetSets) {
            if ($we->status !== 'completed' && $we->status !== 'skipped') {
                $we->update(['status' => 'completed']);
                $isCompleted = true;
            } else {
                 $isCompleted = ($we->status === 'completed');
            }
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Set saved',
            'next_set' => $nextSetNumber,
            'suggestion' => $suggestion,
            'exercise_completed' => $isCompleted
        ]);
    }
    
    /**
     * Get baseline suggestion (for Set 1 loading).
     */
    public function suggest(Request $request, Workout $workout)
    {
        $weId = $request->query('workout_exercise_id');
        $setNum = $request->query('set_number', 1);
        
        $we = WorkoutExercise::where('workout_id', $workout->id)->where('id', $weId)->firstOrFail();
         if ($workout->user_id !== auth()->id()) abort(403);

        $suggestion = $this->suggestionService->getSuggestion($we, $setNum);

        return response()->json($suggestion);
    }
}
