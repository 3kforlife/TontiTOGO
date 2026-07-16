<?php

namespace App\Models;

use App\Enums\Gender;
use App\Enums\MemberStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Member extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'member_code',
        'notebook_number',
        'firstname',
        'lastname',
        'phone',
        'gender',
        'address',
        'status',
        'created_by_agent_id',
    ];

    protected function casts(): array
    {
        return [
            'gender' => Gender::class,
            'status' => MemberStatus::class,
        ];
    }

  
    // Relations
  
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function createdByAgent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by_agent_id');
    }

    public function tontineParticipations(): HasMany
    {
        return $this->hasMany(TontineParticipant::class);
    }

    // Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', MemberStatus::Active->value);
    }

    public function scopeSuspended(Builder $query): Builder
    {
        return $query->where('status', MemberStatus::Suspended->value);
    }

    public function scopeForOrganization(Builder $query, int $organizationId): Builder
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeSearch(Builder $query, string $term): Builder
    {
        return $query->where(function (Builder $q) use ($term) {
            $q->where('firstname', 'like', "%{$term}%")
              ->orWhere('lastname', 'like', "%{$term}%")
              ->orWhere('phone', 'like', "%{$term}%")
              ->orWhere('member_code', 'like', "%{$term}%")
              ->orWhere('notebook_number', 'like', "%{$term}%");
        });
    }

    // Helpers

    public function getFullNameAttribute(): string
    {
        return "{$this->firstname} {$this->lastname}";
    }

    public function isActive(): bool
    {
        return $this->status === MemberStatus::Active;
    }
}
