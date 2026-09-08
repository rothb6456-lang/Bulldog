<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class ImportAuditLog extends Model
{
    use HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'historical_import_id',
        'user_id',
        'action',
        'metadata_json',
    ];

    protected $casts = [
        'metadata_json' => 'array',
    ];
}