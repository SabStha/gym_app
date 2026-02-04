<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Routine;
use App\Models\RoutineDay;
use App\Models\DayExercise;
use App\Models\Exercise;
use App\Models\Workout;
use App\Models\WorkoutExercise;
use App\Models\WorkoutSet;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class HistorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Get or Create Demo User
        $user = User::firstOrCreate(
            ['email' => 'demo@example.com'],
            ['name' => 'Graduation User', 'password' => bcrypt('password')]
        );

        // 2. Create a realistic Split Routine (if not exists)
        $routine = Routine::create([
            'user_id' => $user->id,
            'title' => 'PPL Progression (Demo)',
            'note' => 'Standard Push Pull Legs split for graduation demo.',
            'is_active' => true,
        ]);

        // Define days and exercises (Assuming standard IDs from ExerciseSeeder or searching by name)
        // We'll search by name to be safe, or fallback to first/random if not found
        $days = [
            'Push' => ['Bench Press (Barbell)', 'Overhead Press (Barbell)', 'Incline Dumbbell Press'],
            'Pull' => ['Lat Pulldown', 'Barbell Row', 'Bicep Curl (Dumbbell)'],
            'Legs' => ['Squat (Barbell)', 'Deadlift (Barbell)', 'Leg Press'],
        ];

        $routineDays = [];
        $orderIndex = 1;

        foreach ($days as $dayName => $exercisesNames) {
            $rDay = RoutineDay::create([
                'routine_id' => $routine->id,
                'day_name' => $dayName,
                'order_index' => $orderIndex++,
            ]);
            $routineDays[$dayName] = $rDay;

            $exOrder = 1;
            foreach ($exercisesNames as $name) {
                $ex = Exercise::where('name', 'like', "%$name%")->first();
                if (!$ex) $ex = Exercise::factory()->create(['name' => $name]); // Fallback

                DayExercise::create([
                    'routine_day_id' => $rDay->id,
                    'exercise_id' => $ex->id,
                    'order_index' => $exOrder++,
                    'target_sets' => 3,
                    'rep_min' => 8,
                    'rep_max' => 12,
                ]);
            }
        }

        // 3. Generate 12 Weeks of History
        // Start date: 3 months ago
        $startDate = Carbon::now()->subWeeks(12)->startOfWeek();
        
        // Progression Logic:
        // Bench Press starts at 60kg, +2.5kg every 2 weeks
        // Squat starts at 80kg, +5kg every 2 weeks
        // Bicep Curl starts at 10kg, plateaus after week 6
        
        $stats = [
            'Bench Press (Barbell)' => ['weight' => 60, 'inc' => 2.5],
            'Squat (Barbell)' => ['weight' => 80, 'inc' => 5],
            'Deadlift (Barbell)' => ['weight' => 100, 'inc' => 5],
            'Overhead Press (Barbell)' => ['weight' => 40, 'inc' => 1.25], // Micro loading
            'Lat Pulldown' => ['weight' => 45, 'inc' => 2.5],
            'Barbell Row' => ['weight' => 50, 'inc' => 2.5],
            'Leg Press' => ['weight' => 120, 'inc' => 10],
            'Incline Dumbbell Press' => ['weight' => 20, 'inc' => 2],
            'Bicep Curl (Dumbbell)' => ['weight' => 10, 'inc' => 0], // Will handle specially
        ];

        for ($week = 0; $week < 12; $week++) {
            $weekDate = $startDate->copy()->addWeeks($week);

            // Simulate 3 workouts per week: Mon (Push), Wed (Pull), Fri (Legs)
            $schedule = [
                'Push' => $weekDate->copy()->addDays(0),
                'Pull' => $weekDate->copy()->addDays(2),
                'Legs' => $weekDate->copy()->addDays(4),
            ];

            foreach ($schedule as $type => $date) {
                // Skip if date is in future
                if ($date->isFuture()) continue;

                $rDay = $routineDays[$type];
                
                // Create Workout
                $workout = Workout::create([
                    'user_id' => $user->id,
                    'routine_day_id' => $rDay->id,
                    'workout_date' => $date,
                    'status' => 'completed',
                    'duration_min' => rand(45, 75),
                    'note' => ($week % 4 == 0) ? 'Feeling strong today!' : null,
                ]);

                // Create Exercises & Sets
                foreach ($rDay->dayExercises as $de) {
                    $exercise = $de->exercise;
                    $stat = $stats[$exercise->name] ?? ['weight' => 20, 'inc' => 0];
                    
                    // Simple Linear Progression
                    $progressWeeks = floor($week / 2); // Increase every 2 weeks
                    $currentWeight = $stat['weight'] + ($progressWeeks * $stat['inc']);

                    // Simulate Plateau for Bicep Curl
                    if ($exercise->name == 'Bicep Curl (Dumbbell)' && $week > 6) {
                        $currentWeight = 12.5; // Stuck at 12.5kg
                    }

                    // Randomize reps slightly (8-12)
                    // If early week of block, lower reps higher weight feel? No keep simple.
                    // If "upgrade week", reps might be lower.
                    $reps = rand(8, 12);
                    if ($week % 2 == 1) $reps = rand(10, 12); // Second week of block, hit reps hard

                    $we = WorkoutExercise::create([
                        'workout_id' => $workout->id,
                        'exercise_id' => $exercise->id,
                        'order_index' => $de->order_index,
                        'difficulty' => ($reps == 12) ? 'hard' : 'ok',
                    ]);

                    // Add 3 sets
                    for ($s = 1; $s <= 3; $s++) {
                        WorkoutSet::create([
                            'workout_exercise_id' => $we->id,
                            'set_number' => $s,
                            'weight_kg' => $currentWeight,
                            'reps' => $reps - ($s - 1), // Fatigue: 12, 11, 10
                        ]);
                    }
                }
            }
        }
    }
}
