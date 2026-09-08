<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Milestone extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'milestone_type',
        'scope_type',
        'value_reached',
        'official_status',
        'source_type',
        'fidelity_level',
        'metadata_json',
        'detected_at',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'value_reached' => 'float',
        'detected_at' => 'datetime',
    ];
}