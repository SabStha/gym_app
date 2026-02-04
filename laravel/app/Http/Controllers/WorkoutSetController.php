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
        WorkoutSet::updateOrCreate(
            [
                'workout_exercise_id' => $we->id,
                'set_number' => $validated['set_number']
            ],
            [
                'weight_kg' => $validated['weight_kg'],
                'reps' => $validated['reps']
            ]
        );

        // Calculate Suggestion for the NEXT set
        $nextSetNumber = $validated['set_number'] + 1;
        $suggestion = $this->suggestionService->getSuggestion($we, $nextSetNumber);

        return response()->json([
            'status' => 'success',
            'message' => 'Set saved',
            'next_set' => $nextSetNumber,
            'suggestion' => $suggestion,
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
