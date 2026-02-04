<?php

namespace App\Services;

use App\Models\User;
use App\Models\Exercise;
use App\Models\DayExercise;

class ProgressionService
{
    /**
     * Calculate status and recommendation based on history.
     *
     * @param User $user
     * @param Exercise $exercise
     * @param array $history Recent session stats (ordered DESC by date), each item: ['date' => ..., 'weight' => ..., 'reps' => ...]
     * @return array
     */
    public function analyze(User $user, Exercise $exercise, array $history)
    {
        if (empty($history)) {
            return [
                'status' => 'New',
                'recommendation' => 'Establish a baseline. Start light and find your working weight.',
                'next_weight' => null,
                'next_reps' => null,
            ];
        }

        $lastSession = $history[0];
        $lastWeight = $lastSession['weight'];
        $lastReps = $lastSession['reps'];

        // Get defaults
        $profile = $user->userProfile;
        $increment = $profile ? $profile->default_increment_kg : 2.5;
        
        // Try to find rep range from an active routine day
        // This is a heuristic: pick the first occurrence in an active routine
        $dayExercise = DayExercise::where('exercise_id', $exercise->id)
            ->whereHas('routineDay.routine', function ($q) use ($user) {
                $q->where('user_id', $user->id)->where('is_active', true);
            })
            ->first();

        $repMin = $dayExercise ? $dayExercise->rep_min : 8;
        $repMax = $dayExercise ? $dayExercise->rep_max : 12;

        // Check for Plateau (Last 3 sessions including this one have no improvement)
        $isPlateau = false;
        if (count($history) >= 3) {
            // Simple plateau definition: Weight hasn't increased, and reps haven't increased 
            // over the LAST 3 sessions (index 0, 1, 2)
            // Actually, usually means stuck at same weight/reps for 3 sessions
            $s0 = $history[0];
            $s1 = $history[1];
            $s2 = $history[2];

            if ($s0['weight'] <= $s1['weight'] && $s1['weight'] <= $s2['weight'] &&
                $s0['reps'] <= $s1['reps'] && $s1['reps'] <= $s2['reps']) {
                $isPlateau = true;
            }
        }

        if ($isPlateau) {
            return [
                'status' => 'Plateau',
                'recommendation' => "Stuck for 3 sessions. Deload recommended: Reduce weight to " . ($lastWeight * 0.9) . "kg for next session to recover.",
                'next_weight' => round($lastWeight * 0.9, 1),
                'next_reps' => $repMin,
            ];
        }

        // Regression check (if reps dropped significantly below min without weight increase)
        if ($lastReps < $repMin) {
            return [
                'status' => 'Regress',
                'recommendation' => 'Missed rep target. Deload by 10% to rebuild form and volume.',
                'next_weight' => round($lastWeight * 0.9, 1),
                'next_reps' => $repMin,
            ];
        }

        // Progression Logic
        if ($lastReps >= $repMax) {
            $nextWeight = $lastWeight + $increment;
            return [
                'status' => 'Progressing',
                'recommendation' => "Hit max reps ($lastReps)! Increase weight by {$increment}kg.",
                'next_weight' => $nextWeight,
                'next_reps' => $repMin, // Reset to bottom of range
            ];
        } elseif ($lastReps >= $repMin) {
            return [
                'status' => 'Progressing',
                'recommendation' => "Within rep range ($repMin-$repMax). Keep weight, try to add 1 rep.",
                'next_weight' => $lastWeight,
                'next_reps' => $lastReps + 1,
            ];
        }

        // Fallback (should be covered by < repMin check, but just in case)
        return [
            'status' => 'Maintain',
            'recommendation' => 'Keep current settings.',
            'next_weight' => $lastWeight,
            'next_reps' => $lastReps,
        ];
    }
}
