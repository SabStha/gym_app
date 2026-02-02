<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DayExercise extends Model
{
    use HasFactory;

    protected $fillable = [
        'routine_day_id',
        'exercise_id',
        'order_index',
        'target_sets',
        'rep_min',
        'rep_max',
        'increment_override_kg',
    ];

    public function routineDay()
    {
        return $this->belongsTo(RoutineDay::class);
    }

    public function exercise()
    {
        return $this->belongsTo(Exercise::class);
    }
}
