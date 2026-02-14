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
        $user = auth()->user();

        // --- 1. Exercise Progress (Existing) ---
        $exercises = Exercise::whereHas('workoutExercises.workout', function ($query) use ($user) {
            $query->where('user_id', $user->id)
                  ->where('status', 'completed');
        })
        ->orderBy('name')
        ->get();

        $selectedExercise = null;
        $history = [];
        $analysis = null;
        $strengthChartData = ['labels' => [], 'data' => []];

        if ($request->has('exercise_id')) {
            $selectedExercise = Exercise::find($request->exercise_id);

            if ($selectedExercise) {
                // Load last 5 completed sessions
                $workouts = Workout::where('user_id', $user->id)
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
                    $we = $workout->workoutExercises->first(); 
                    if (!$we) continue;

                    $bestSet = $we->workoutSets->sortByDesc(function ($set) {
                        return $set->weight_kg * 1000 + $set->reps; 
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
                $reversedHistory = array_reverse($history);
                foreach ($reversedHistory as $h) {
                    $strengthChartData['labels'][] = $h['date']->format('M d');
                    $strengthChartData['data'][] = $h['weight'];
                }

                // Analyze
                $analysis = $this->progressionService->analyze($user, $selectedExercise, $history);
            }
        }

        // --- 2. Body & Nutrition Analysis (New) ---
        
        // A. Weight Logs (Last 30 Days)
        $weightLogs = \App\Models\UserWeightLog::where('user_id', $user->id)
            ->whereDate('date', '>=', now()->subDays(30))
            ->orderBy('date', 'asc')
            ->get();
            
        $weightChartData = [
            'labels' => $weightLogs->pluck('date')->map(fn($d) => $d->format('M d'))->toArray(),
            'data' => $weightLogs->pluck('weight')->toArray(),
        ];
        
        // B. Nutrition (Last 7 Days)
        $nutritionLogs = \App\Models\FoodEntry::where('user_id', $user->id)
            ->whereDate('date', '>=', now()->subDays(7))
            ->get()
            ->groupBy(fn($e) => $e->date->format('Y-m-d'));
            
        $calorieChartData = ['labels' => [], 'actual' => [], 'target' => []];
        $macroAverages = ['protein' => 0, 'carbs' => 0, 'fat' => 0];
        $daysCount = 0;
        
        $period = \Carbon\CarbonPeriod::create(now()->subDays(6), now());
        
        foreach ($period as $date) {
            $d = $date->format('Y-m-d');
            $dayEntries = $nutritionLogs->get($d, collect());
            
            $actualCals = $dayEntries->sum('calories');
            $targetCals = $user->userProfile->target_calories ?? 2000;
            
            $calorieChartData['labels'][] = $date->format('D'); // Mon, Tue...
            $calorieChartData['actual'][] = $actualCals;
            $calorieChartData['target'][] = $targetCals;
            
            if ($dayEntries->isNotEmpty()) {
                $daysCount++;
                $macroAverages['protein'] += $dayEntries->sum('protein');
                $macroAverages['carbs'] += $dayEntries->sum('carbs');
                $macroAverages['fat'] += $dayEntries->sum('fat');
            }
        }
        
        if ($daysCount > 0) {
            $macroAverages['protein'] /= $daysCount;
            $macroAverages['carbs'] /= $daysCount;
            $macroAverages['fat'] /= $daysCount;
        }

        return view('progress.index', compact(
            'exercises', 'selectedExercise', 'history', 'analysis', 'strengthChartData',
            'weightLogs', 'weightChartData', 'calorieChartData', 'macroAverages'
        ));
    }
}
