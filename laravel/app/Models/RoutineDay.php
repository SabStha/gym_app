<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoutineDay extends Model
{
    use HasFactory;

    protected $fillable = [
        'routine_id',
        'day_name',
        'order_index',
    ];

    public function routine()
    {
        return $this->belongsTo(Routine::class);
    }

    public function dayExercises()
    {
        return $this->hasMany(DayExercise::class);
    }
}
