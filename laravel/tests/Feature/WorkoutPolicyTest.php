<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\User;
use App\Models\Workout;
use App\Models\Routine;
use App\Models\RoutineDay;

class WorkoutPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_view_others_workout()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $workout = Workout::factory()->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user1)->get(route('workouts.show', $workout));

        $response->assertStatus(403);
    }

    public function test_user_cannot_update_others_workout()
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        
        $workout = Workout::factory()->create(['user_id' => $user2->id, 'status' => 'in_progress']);

        $response = $this->actingAs($user1)->post(route('workouts.finish', $workout), [
            'note' => 'Hacked'
        ]);

        $response->assertStatus(403);
    }
}
