<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Exercise>
 */
class ExerciseFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => $this->faker->words(3, true),
            'muscle_group' => $this->faker->randomElement(['chest', 'back', 'legs', 'shoulders', 'arms', 'core']),
            'is_default' => false,
            'user_id' => null, // Or create a user if strict
        ];
    }
}
