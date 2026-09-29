<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class GuardianRelationship extends Model
{
    use HasUuid;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $guarded = ['id'];
    protected $casts = ['expires_at' => 'datetime', 'accepted_at' => 'datetime', 'confirmed_at' => 'datetime'];

    public function playerIdentity() { return $this->belongsTo(PlayerIdentity::class); }
    public function team() { return $this->belongsTo(Team::class); }
}
