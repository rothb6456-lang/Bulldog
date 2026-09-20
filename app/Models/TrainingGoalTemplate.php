<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingGoalTemplate extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'code',
        'title',
        'description',
        'category',
        'metric_key',
        'default_target_unit',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function goals(): HasMany
    {
        return $this->hasMany(PlayerTrainingGoal::class, 'goal_template_id');
    }
}
