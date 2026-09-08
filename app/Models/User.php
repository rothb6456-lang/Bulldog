<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use CanResetPassword, HasApiTokens, HasFactory, Notifiable, HasUuid;

    protected $keyType = 'string';
    public $incrementing = false;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_minor',
        'status',
    ];

    /**
     * The attributes that should be hidden for serialization.
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_minor' => 'boolean',
    ];

    /**
     * Get the profile associated with the user.
     */
    public function profile(): HasOne
    {
        return $this->hasOne(UserProfile::class);
    }

    /**
     * Get the claimed Player Identity associated with this user.
     */
    public function playerIdentity(): HasOne
    {
        return $this->hasOne(PlayerIdentity::class, 'user_id');
    }

    /**
     * Get the Coach Identity associated with this user.
     */
    public function coachIdentity(): HasOne
    {
        return $this->hasOne('App\\Models\\CoachIdentity', 'user_id');
    }

    /**
     * Get guardian relationships managed by this user.
     */
    public function guardianRelationships(): HasMany
    {
        return $this->hasMany('App\\Models\\GuardianRelationship', 'guardian_user_id');
    }
}
