<?php

namespace App\Services;

use App\Models\User;
use App\Models\Exercise;
use App\Models\WorkoutExercise;
use App\Models\WorkoutSet;
use Illuminate\Support\Collection;

class ProgressionSuggestionService
{
    /**
     * Get suggestions for a specific workout exercise.
     * 
     * @param WorkoutExercise $workoutExercise
     * @param int $setNumber The set we are targeting (1, 2, 3...)
     * @return array
     */
    public function getSuggestion(WorkoutExercise $workoutExercise, int $setNumber): array
    {
        $exercise = $workoutExercise->exercise;
        $user = $workoutExercise->workout->user;

        // Determine Increment
        $increment = $this->getIncrement($exercise, $user);

        // Step 0: Baseline (if Set 1)
        if ($setNumber === 1) {
            return $this->getSet1Suggestion($user, $exercise, $increment);
        }

        // Get current session's previous sets
        $currentSets = $workoutExercise->workoutSets()->orderBy('set_number')->get();

        if ($setNumber === 2) {
            return $this->getSet2Suggestion($currentSets, $increment);
        }

        if ($setNumber === 3) {
            return $this->getSet3Suggestion($currentSets, $increment);
        }

        // Fallback for > Set 3
        return [
            'type' => 'maintain',
            'weight' => null,
            'reps' => '6-10',
            'reason' => 'Maintain form',
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

    private function getSet1Suggestion(User $user, Exercise $exercise, float $increment): array
    {
        // Fetch last finished workout for this exercise
        $lastWorkoutExercise = WorkoutExercise::whereHas('workout', function ($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->where('status', 'completed');
            })
            ->where('exercise_id', $exercise->id)
            ->latest() // Get most recent (which joins mostly on ID or created_at, ideally verify specific date but latest is acceptable shortcut)
            ->first();

        if (!$lastWorkoutExercise) {
            return [
                'type' => 'baseline',
                'weight' => null,
                'reps' => '8-10',
                'reason' => 'Start with comfortable working weight',
            ];
        }

        // Get top set (highest weight)
        $topSet = $lastWorkoutExercise->workoutSets()
            ->orderByDesc('weight_kg')
            ->orderByDesc('reps')
            ->first();

        if (!$topSet) {
             return [
                'type' => 'baseline',
                'weight' => null,
                'reps' => '8-10',
                'reason' => 'No history found',
            ];
        }

        $lastWeight = $topSet->weight_kg;
        $lastReps = $topSet->reps;

        // Rules
        if ($lastReps >= 10) {
            return [
                'type' => 'increase',
                'weight' => $lastWeight + $increment,
                'reps' => '6-10',
                'reason' => "Last time: {$lastWeight}kg x {$lastReps} (Strong!)",
            ];
        }

        if ($lastReps >= 6) { // 6-9
            return [
                'type' => 'maintain',
                'weight' => $lastWeight,
                'reps' => '6-10',
                'reason' => "Last time: {$lastWeight}kg x {$lastReps}",
            ];
        }

        // < 6
        $newWeight = max(0, $lastWeight - $increment);
        return [
            'type' => 'decrease',
            'weight' => $newWeight,
            'reps' => '6-10',
            'reason' => "Last time: {$lastWeight}kg x {$lastReps} (Struggled)",
        ];
    }

    private function getSet2Suggestion(Collection $currentSets, float $increment): array
    {
        // We need Set 1 result
        $set1 = $currentSets->firstWhere('set_number', 1);
        
        if (!$set1) return ['type' => 'error', 'weight' => null, 'reps' => null, 'reason' => 'Log Set 1 first'];

        $w1 = $set1->weight_kg;
        $r1 = $set1->reps;

        if ($r1 >= 10) {
            return [
                'type' => 'maintain',
                'weight' => $w1,
                'reps' => '8-10',
                'reason' => "Set 1 was easy ($r1 reps)",
            ];
        }

        if ($r1 >= 6) { // 6-9
            return [
                'type' => 'maintain',
                'weight' => $w1,
                'reps' => $r1, // Matches R1
                'reason' => "Set 1 solid ($r1 reps)",
            ];
        }

        // < 6
        $newWeight = max(0, $w1 - $increment);
        return [
            'type' => 'decrease',
            'weight' => $newWeight,
            'reps' => '6-8',
            'reason' => "Set 1 too heavy ($r1 reps)",
        ];
    }

    private function getSet3Suggestion(Collection $currentSets, float $increment): array
    {
        $set1 = $currentSets->firstWhere('set_number', 1);
        $set2 = $currentSets->firstWhere('set_number', 2);

        if (!$set1 || !$set2) return ['type' => 'error', 'weight' => null, 'reps' => null, 'reason' => 'Log previous sets'];

        $r1 = $set1->reps;
        $r2 = $set2->reps;
        $w2 = $set2->weight_kg;

        // If r2 dropped >= 2 reps vs r1
        if (($r1 - $r2) >= 2) {
             $newWeight = max(0, $w2 - $increment);
             return [
                'type' => 'decrease',
                'weight' => $newWeight,
                'reps' => '6-8',
                'reason' => "Significant drop-off ($r1 -> $r2)",
            ];
        }

        return [
            'type' => 'maintain',
            'weight' => $w2,
            'reps' => '6-8',
            'reason' => "Performance stable",
        ];
    }
}
