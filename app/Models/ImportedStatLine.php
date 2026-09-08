<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ImportedStatLine extends Model
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'historical_import_id',
        'subject_type',
        'subject_id',
        'season_key',
        'game_date',
        'stat_blob_json',
        'source_row_identifier',
    ];

    protected $casts = [
        'stat_blob_json' => 'array',
        'game_date' => 'date',
    ];

    public function historicalImport(): BelongsTo
    {
        return $this->belongsTo(HistoricalImport::class);
    }

    public function subject()
    {
        return $this->morphTo();
    }
}