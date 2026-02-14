<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserWeightLog extends Model
{
    protected $fillable = ['user_id', 'weight', 'date'];

    protected $casts = [
        'date' => 'date',
        'weight' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
