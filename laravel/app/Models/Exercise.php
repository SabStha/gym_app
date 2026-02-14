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
        'image_url',
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

    public function getImageUrlAttribute()
    {
        // 1. Direct URL
        if (!empty($this->attributes['image_url'])) {
            return $this->attributes['image_url'];
        }

        // 2. Local File (Slug)
        $slug = \Illuminate\Support\Str::slug($this->name);
        $localPath = "images/exercises/{$slug}.webp";
        
        if (file_exists(public_path($localPath))) {
            return asset($localPath);
        }

        // 3. Default Fallback
        return asset('images/exercises/default.webp');
    }

    public function workoutExercises()
    {
        return $this->hasMany(WorkoutExercise::class);
    }
}
