<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimelineEntry extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'subject_type',
        'subject_id',
        'entry_type',
        'entry_date',
        'title',
        'description',
        'source_type',
        'source_ref_id',
        'official_status',
        'visibility',
    ];

    protected $casts = [
        'entry_date' => 'datetime',
    ];
}