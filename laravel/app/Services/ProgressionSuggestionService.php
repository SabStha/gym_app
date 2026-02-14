<?php

namespace App\Services;

use App\Models\User;
use App\Models\Exercise;
use App\Models\WorkoutExercise;
use Illuminate\Support\Collection;

class ProgressionSuggestionService
{
    /**
     * Get suggestions for a specific workout exercise.
     * Use EXACT usage history from the previous session.
     * 
     * @param WorkoutExercise $workoutExercise
     * @param int $setNumber The set we are targeting (1, 2, 3...)
     * @return array|null
     */
    public function getSuggestion(WorkoutExercise $workoutExercise, int $setNumber): array
    {
        $exercise = $workoutExercise->exercise;
        $user = $workoutExercise->workout->user;
        $increment = $this->getIncrement($exercise, $user);

        // 1. Fetch LAST completed session for this exercise
        $lastWe = WorkoutExercise::whereHas('workout', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('status', 'completed');
            })
            ->where('exercise_id', $exercise->id)
            ->latest()
            ->first();

        // No History -> Baseline / No Suggestion
        if (!$lastWe) {
            return [
                'type' => 'baseline',
                'weight' => null, // Let frontend handle empty
                'reps' => '8-10',
                'reason' => 'First time! Find your baseline.',
            ];
        }

        // 2. Find the EXACT set from last time
        // "For Set 2, it must use previous workout’s Set 2"
        $lastSet = $lastWe->workoutSets()->where('set_number', $setNumber)->first();

        // Fallback: If Set 3 requested but last time only did 2 sets, use Set 2 data?
        if (!$lastSet) {
            $lastSet = $lastWe->workoutSets()->orderByDesc('set_number')->first();
        }

        // If still no sets found (empty workout?)
        if (!$lastSet) {
             return [
                'type' => 'baseline',
                'weight' => null,
                'reps' => '8-10',
                'reason' => 'No sets logged last time',
            ];
        }

        $lastWeight = $lastSet->weight_kg;
        $lastReps = $lastSet->reps;

        // 3. Strict Progression Logic
        // Top Range Trigger: 10 Reps (User Requirement: "If last time same weight hit top reps")
        if ($lastReps >= 10) {
            $newWeight = $lastWeight + $increment;
            return [
                'type' => 'increase',
                'weight' => $newWeight,
                'suggest_min' => 6,
                'suggest_max' => 8,
                'apply_reps' => 6,
                'reason' => "Acetime: {$lastWeight}kg x {$lastReps} -> Level Up!",
            ];
        }

        // Below Target Trigger: < 10 Reps
        // User Requirement: "If last time reps are below target -> keep weight and suggest reps higher"
        $minReps = $lastReps + 1;
        $maxReps = $lastReps + 2;
        
        return [
            'type' => 'maintain',
            'weight' => $lastWeight,
            'suggest_min' => $minReps,
            'suggest_max' => $maxReps,
            'apply_reps' => $minReps,
            'reason' => "Last time: {$lastWeight}kg x {$lastReps}. Beat it!",
        ];
    }

    private function getIncrement(Exercise $exercise, User $user)
    {
        switch ($exercise->equipment_type) {
            case 'barbell': return 2.5;
            case 'dumbbell': return 1.0;
            case 'machine': return 5.0;
            default: return $user->userProfile->default_increment_kg ?? 2.5;
        }
    }
}
