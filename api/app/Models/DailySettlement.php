<?php

namespace App\Models;

use App\Enums\SettlementStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailySettlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'agent_id',
        'validated_by_responsible_id',
        'date_settled',
        'expected_amount',
        'received_amount',
        'status',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'date_settled'    => 'date',
            'expected_amount' => 'decimal:2',
            'received_amount' => 'decimal:2',
            'status'          => SettlementStatus::class,
        ];
    }

    // Relations

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(User::class, 'agent_id');
    }

    public function validatedByResponsible(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by_responsible_id');
    }

    // Scopes

    public function scopeValidated(Builder $query): Builder
    {
        return $query->where('status', SettlementStatus::Validated->value);
    }

    public function scopeWithDiscrepancy(Builder $query): Builder
    {
        return $query->where('status', SettlementStatus::Discrepancy->value);
    }

    public function scopeForAgent(Builder $query, int $agentId): Builder
    {
        return $query->where('agent_id', $agentId);
    }

    public function scopeForDate(Builder $query, string $date): Builder
    {
        return $query->where('date_settled', $date);
    }

    // Helpers

    public function getDiscrepancyAttribute(): float
    {
        return (float) ($this->received_amount - $this->expected_amount);
    }

    public function hasDiscrepancy(): bool
    {
        return $this->status === SettlementStatus::Discrepancy;
    }
}
