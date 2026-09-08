<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShareLink extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'object_type',
        'object_id',
        'token',
        'created_by_user_id',
        'expires_at',
        'is_active',
        'allow_authenticated_only',
        'metadata_json',
    ];

    protected $casts = [
        'metadata_json' => 'array',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
        'allow_authenticated_only' => 'boolean',
    ];
}