<?php

namespace App\Models;

use App\Enums\ContributionSettlementStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Contribution extends Model
{
    use HasFactory;

    protected $fillable = [
        'tontine_participant_id',
        'user_id',
        'reference',
        'amount',
        'latitude',
        'longitude',
        'settlement_status',
    ];

    protected function casts(): array
    {
        return [
            'amount'            => 'decimal:2',
            'latitude'          => 'decimal:8',
            'longitude'         => 'decimal:8',
            'settlement_status' => ContributionSettlementStatus::class,
        ];
    }

    // Relations

    public function tontineParticipant(): BelongsTo
    {
        return $this->belongsTo(TontineParticipant::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes

    public function scopePending(Builder $query): Builder
    {
        return $query->where('settlement_status', ContributionSettlementStatus::Pending->value);
    }

    public function scopeSettled(Builder $query): Builder
    {
        return $query->where('settlement_status', ContributionSettlementStatus::Settled->value);
    }

    public function scopeCollectedToday(Builder $query): Builder
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeCollectedThisWeek(Builder $query): Builder
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeCollectedThisMonth(Builder $query): Builder
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    public function scopeFilterByPeriod(Builder $query, ?string $date): Builder
    {
        return $date ? $query->whereDate('created_at', $date) : $query;
    }

    public function scopeForOrganization(Builder $query, int $organizationId): Builder
    {
        return $query->whereHas('tontineParticipant.tontine', fn(Builder $q) =>
            $q->where('organization_id', $organizationId)
        );
    }

    public function scopeGeolocated(Builder $query): Builder
    {
        return $query->whereNotNull('latitude')->whereNotNull('longitude');
    }

    // Helpers

    public function isPending(): bool
    {
        return $this->settlement_status === ContributionSettlementStatus::Pending;
    }

    public function isSettled(): bool
    {
        return $this->settlement_status === ContributionSettlementStatus::Settled;
    }

    public function getMemberAttribute(): ?Member
    {
        return $this->tontineParticipant?->member;
    }
}
