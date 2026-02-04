<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Workout extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'routine_day_id',
        'workout_date',
        'started_at',
        'finished_at',
        'duration_min',
        'status',
        'note',
    ];

    protected $casts = [
        'workout_date' => 'datetime',
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    /**
     * Get the workout duration in minutes relative to start/finish timestamps.
     */
    public function getDurationMinutesAttribute()
    {
        if ($this->started_at && $this->finished_at) {
            return $this->started_at->diffInMinutes($this->finished_at);
        }

        if ($this->duration_min) {
            return $this->duration_min;
        }

        return '--';
    }

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
