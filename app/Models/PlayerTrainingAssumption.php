<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerTrainingAssumption extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'player_identity_id',
        'assumption_code',
        'category',
        'description',
        'confidence',
        'confidence_level',
        'phase_first_observed',
        'first_observed',
        'last_validated',
        'status',
        'supporting_evidence',
        'phase_id_first_observed',
        'notes',
    ];

    protected $casts = [
        'first_observed' => 'date',
        'last_validated' => 'date',
    ];

    public function playerIdentity(): BelongsTo
    {
        return $this->belongsTo(PlayerIdentity::class, 'player_identity_id');
    }

    public function phaseFirstObserved(): BelongsTo
    {
        return $this->belongsTo(TrainingPhase::class, 'phase_id_first_observed');
    }
}
