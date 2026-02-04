<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'muscle_group',
        'is_default',
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function dayExercises()
    {
        return $this->hasMany(DayExercise::class);
    }

    public function workoutExercises()
    {
        return $this->hasMany(WorkoutExercise::class);
    }
}
