<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AchievementAward extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'achievement_definition_id',
        'subject_type',
        'subject_id',
        'official_status',
        'source_type',
        'metadata_json',
        'awarded_at',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'awarded_at' => 'datetime',
    ];

    public function definition(): BelongsTo
    {
        return $this->belongsTo(AchievementDefinition::class, 'achievement_definition_id');
    }
}