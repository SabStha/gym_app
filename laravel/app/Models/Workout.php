<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Workout extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'routine_day_id',
        'workout_date',
        'duration_min',
        'status',
        'note',
    ];

    protected $casts = [
        'workout_date' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function routineDay()
    {
        return $this->belongsTo(RoutineDay::class);
    }

    public function workoutExercises()
    {
        return $this->hasMany(WorkoutExercise::class)->orderBy('order_index');
    }
}
