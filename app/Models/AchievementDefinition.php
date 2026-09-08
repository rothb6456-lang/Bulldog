<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class AchievementDefinition extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'name',
        'description',
        'category',
        'rule_json',
        'xp_value',
        'is_repeatable',
        'visibility_default',
    ];

    protected $casts = [
        'rule_json' => 'array',
        'is_repeatable' => 'boolean',
        'xp_value' => 'integer',
    ];

    public function awards(): HasMany
    {
        return $this->hasMany(AchievementAward::class);
    }
}