<?php

namespace App\Models;

use App\Enums\ParticipantStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TontineParticipant extends Model
{
    use HasFactory;

    protected $fillable = [
        'tontine_id',
        'member_id',
        'chosen_amount',
        'joined_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'joined_at'     => 'date',
            'chosen_amount' => 'decimal:2',
            'status'        => ParticipantStatus::class,
        ];
    }

    // Relations


    public function tontine(): BelongsTo
    {
        return $this->belongsTo(Tontine::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function contributions(): HasMany
    {
        return $this->hasMany(Contribution::class);
    }

    // Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', ParticipantStatus::Active->value);
    }

    public function scopeCancelled(Builder $query): Builder
    {
        return $query->where('status', ParticipantStatus::Cancelled->value);
    }

    // Helpers

    public function getTotalContributionsAttribute(): float
    {
        return (float) $this->contributions()->sum('amount');
    }

    public function isActive(): bool
    {
        return $this->status === ParticipantStatus::Active;
    }
}
