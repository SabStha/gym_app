<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Workout;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Workout>
 */
class WorkoutFactory extends Factory
{
    protected $model = Workout::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'routine_day_id' => null, // or create one if needed
            'workout_date' => now(),
            'started_at' => now(),
            'finished_at' => null,
            'duration_min' => null,
            'status' => 'in_progress',
            'note' => $this->faker->sentence(),
        ];
    }
}
