<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FoodEntry extends Model
{
    protected $fillable = [
        'user_id',
        'food_id',
        'grams',
        'date',
        'calories',
        'protein',
        'carbs',
        'fat',
    ];

    protected $casts = [
        'date' => 'date:Y-m-d',
        'protein' => 'decimal:2',
        'carbs' => 'decimal:2',
        'fat' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function food()
    {
        return $this->belongsTo(Food::class);
    }
}
