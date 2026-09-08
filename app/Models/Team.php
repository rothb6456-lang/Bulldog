<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    use HasFactory, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'team_code',
        'name',
        'sport_id',
        'age_group',
        'season_label',
        'created_by_user_id',
        'status',
    ];

    /**
     * Get the sport this team plays.
     */
    public function sport(): BelongsTo
    {
        return $this->belongsTo(Sport::class);
    }

    /**
     * Get the memberships/roster slots belonging to this team.
     */
    public function memberships(): HasMany
    {
        return $this->hasMany(TeamMembership::class);
    }

    /**
     * Get the active role assignments for permission authorization.
     */
    public function roleAssignments(): HasMany
    {
        return $this->hasMany(TeamRoleAssignment::class);
    }
}