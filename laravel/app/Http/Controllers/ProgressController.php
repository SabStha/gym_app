<?php

namespace App\Http\Controllers;

use App\Models\Exercise;
use App\Models\Workout;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Services\ProgressionService;

class ProgressController extends Controller
{
    protected $progressionService;

    public function __construct(ProgressionService $progressionService)
    {
        $this->progressionService = $progressionService;
    }

    public function index(Request $request)
    {
        // Get list of exercises the user has actually performed in completed workouts
        // We join through workout_exercises -> workouts
        $exercises = Exercise::whereHas('workoutExercises.workout', function ($query) {
            $query->where('user_id', auth()->id())
                  ->where('status', 'completed');
        })
        ->orderBy('name')
        ->get();

        $selectedExercise = null;
        $history = [];
        $analysis = null;

        if ($request->has('exercise_id')) {
            $selectedExercise = Exercise::find($request->exercise_id);

            if ($selectedExercise) {
                // Load last 5 completed sessions
                $workouts = Workout::where('user_id', auth()->id())
                    ->where('status', 'completed')
                    ->whereHas('workoutExercises', function ($q) use ($selectedExercise) {
                        $q->where('exercise_id', $selectedExercise->id);
                    })
                    ->with(['workoutExercises' => function ($q) use ($selectedExercise) {
                        $q->where('exercise_id', $selectedExercise->id)->with('workoutSets');
                    }])
                    ->orderBy('workout_date', 'desc')
                    ->take(5)
                    ->get();

                // Compute best set for each session
                foreach ($workouts as $workout) {
                    $we = $workout->workoutExercises->first(); // Should be only one per exercise per workout usually
                    if (!$we) continue;

                    $bestSet = $we->workoutSets->sortByDesc(function ($set) {
                        return $set->weight_kg * 1000 + $set->reps; // Heuristic: weight dominant, then reps
                    })->first();

                    if ($bestSet) {
                        $history[] = [
                            'date' => $workout->workout_date,
                            'weight' => $bestSet->weight_kg,
                            'reps' => $bestSet->reps,
                        ];
                    }
                }

                // Chart Data (Ascending for Chart)
                $chartData = [
                    'labels' => [],
                    'data' => []
                ];
                $reversedHistory = array_reverse($history);
                foreach ($reversedHistory as $h) {
                    $chartData['labels'][] = $h['date']->format('M d');
                    $chartData['data'][] = $h['weight'];
                }

                // Analyze
                $analysis = $this->progressionService->analyze(auth()->user(), $selectedExercise, $history);
            }
        }
         
        // Default empty chart data if not set
        if (!isset($chartData)) {
            $chartData = ['labels' => [], 'data' => []];
        }

        return view('progress.index', compact('exercises', 'selectedExercise', 'history', 'analysis', 'chartData'));
    }
}
