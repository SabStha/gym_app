<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'birth_year',
        'sex',
        'height_cm',
        'current_weight_kg',
        'goal_type',
        'target_weight_kg',
        'goal_preset',
        'default_increment_kg',
        'rounding_mode',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
