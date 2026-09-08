<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Record extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'record_scope',
        'subject_type',
        'subject_id',
        'stat_key',
        'record_value',
        'originating_game_id',
        'official_status',
        'source_type',
        'metadata_json',
        'effective_date',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'record_value' => 'float',
        'effective_date' => 'datetime',
    ];
}