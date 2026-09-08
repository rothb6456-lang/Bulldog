<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TeamRoleAssignment extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'team_id',
        'user_id',
        'role_type',
        'granted_by_user_id',
    ];

    /**
     * Get the team this role is assigned on.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the user who has this role.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
