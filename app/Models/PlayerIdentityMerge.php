<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerIdentityMerge extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'canonical_identity_id',
        'duplicate_identity_id',
        'duplicate_player_code',
        'duplicate_display_name',
        'merged_records_manifest',
        'merged_by_user_id',
        'reason',
    ];

    protected $casts = [
        'merged_records_manifest' => 'array',
    ];

    public function canonicalIdentity(): BelongsTo
    {
        return $this->belongsTo(PlayerIdentity::class, 'canonical_identity_id');
    }

    public function mergedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merged_by_user_id');
    }
}