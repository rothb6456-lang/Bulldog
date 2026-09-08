<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HistoricalImport extends Model
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'import_type',
        'context_type',
        'context_id',
        'source_label',
        'fidelity_level',
        'verification_status',
        'uploaded_by_user_id',
        'notes',
    ];

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by_user_id');
    }

    public function statLines(): HasMany
    {
        return $this->hasMany(ImportedStatLine::class);
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(ImportAuditLog::class);
    }
}