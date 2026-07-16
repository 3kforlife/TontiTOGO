<?php

namespace App\Models;

use App\Enums\UserRole;
use App\Enums\UserStatus;
use App\Notifications\ResetPasswordNotification;
use App\Notifications\VerifyEmailNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'firstname',
        'lastname',
        'email',
        'phone',
        'role',
        'status',
        'password',
        'must_change_password',
        'avatar_url',
        'email_verified_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password'             => 'hashed',
            'must_change_password' => 'boolean',
            'role'                 => UserRole::class,
            'status'               => UserStatus::class,
            'email_verified_at'    => 'datetime',
        ];
    }

    // relations

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function registeredMembers(): HasMany
    {
        return $this->hasMany(Member::class, 'created_by_agent_id');
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    public function dailySettlementsAsAgent(): HasMany
    {
        return $this->hasMany(DailySettlement::class, 'agent_id');
    }

    public function dailySettlementsAsResponsible(): HasMany
    {
        return $this->hasMany(DailySettlement::class, 'validated_by_responsible_id');
    }

    // scopes

    public function scopeAgents(Builder $query): Builder
    {
        return $query->where('role', UserRole::Agent->value);
    }

    public function scopeResponsibles(Builder $query): Builder
    {
        return $query->where('role', UserRole::Responsible->value);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', UserStatus::Active->value);
    }

    public function scopeSuspended(Builder $query): Builder
    {
        return $query->where('status', UserStatus::Suspended->value);
    }

    public function scopeForOrganization(Builder $query, int $organizationId): Builder
    {
        return $query->where('organization_id', $organizationId);
    }



    public function isResponsible(): bool
    {
        return $this->role === UserRole::Responsible;
    }

    public function isAgent(): bool
    {
        return $this->role === UserRole::Agent;
    }

    public function isActive(): bool
    {
        return $this->status === UserStatus::Active;
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->firstname} {$this->lastname}";
    }

    public function hasVerifiedEmail(): bool
    {
        if ($this->isAgent()) {
            return true;
        }

        return $this->email_verified_at !== null;
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmailNotification());
    }

    public function sendPasswordResetNotification(#[\SensitiveParameter] $token): void
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function getPhoneForPasswordReset(): ?string
    {
        return $this->phone;
    }
}
