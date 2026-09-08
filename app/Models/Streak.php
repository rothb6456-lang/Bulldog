<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Streak extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'streak_type',
        'current_value',
        'best_value',
        'start_game_id',
        'end_game_id',
        'official_status',
    ];

    protected $casts = [
        'current_value' => 'integer',
        'best_value' => 'integer',
    ];
}