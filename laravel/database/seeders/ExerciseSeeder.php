<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Exercise;

class ExerciseSeeder extends Seeder
{
    public function run()
    {
        $exercises = [
            ['name' => 'Barbell Squat', 'muscle_group' => 'Legs'],
            ['name' => 'Deadlift', 'muscle_group' => 'Back'],
            ['name' => 'Bench Press', 'muscle_group' => 'Chest'],
            ['name' => 'Overhead Press', 'muscle_group' => 'Shoulders'],
            ['name' => 'Pull Up', 'muscle_group' => 'Back'],
            ['name' => 'Dumbbell Row', 'muscle_group' => 'Back'],
            ['name' => 'Dumbbell Lunge', 'muscle_group' => 'Legs'],
            ['name' => 'Leg Press', 'muscle_group' => 'Legs'],
            ['name' => 'Leg Extension', 'muscle_group' => 'Legs'],
            ['name' => 'Hamstring Curl', 'muscle_group' => 'Legs'],
            ['name' => 'Incline Bench Press', 'muscle_group' => 'Chest'],
            ['name' => 'Dumbbell Fly', 'muscle_group' => 'Chest'],
            ['name' => 'Lateral Raise', 'muscle_group' => 'Shoulders'],
            ['name' => 'Face Pull', 'muscle_group' => 'Shoulders'],
            ['name' => 'Barbell Curl', 'muscle_group' => 'Biceps'],
            ['name' => 'Hammer Curl', 'muscle_group' => 'Biceps'],
            ['name' => 'Tricep Pushdown', 'muscle_group' => 'Triceps'],
            ['name' => 'Skull Crusher', 'muscle_group' => 'Triceps'],
            ['name' => 'Calf Raise', 'muscle_group' => 'Calves'],
            ['name' => 'Plank', 'muscle_group' => 'Core'],
            ['name' => 'Romanian Deadlift', 'muscle_group' => 'Legs'],
            ['name' => 'Dips', 'muscle_group' => 'Triceps'],
        ];

        foreach ($exercises as $exercise) {
            Exercise::firstOrCreate(
                ['name' => $exercise['name'], 'user_id' => null], // Match on name + system default (user_id null)
                [
                    'muscle_group' => $exercise['muscle_group'],
                    'is_default' => true
                ]
            );
        }
    }
}
