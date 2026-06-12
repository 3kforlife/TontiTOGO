<?php

namespace App\Models;

use App\Enums\TontineFrequency;
use App\Enums\TontineStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tontine extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'organization_id',
        'name',
        'minimum_amount',
        'frequency',
        'start_date',
        'end_date',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'start_date'     => 'date',
            'end_date'       => 'date',
            'minimum_amount' => 'decimal:2',
            'frequency'      => TontineFrequency::class,
            'status'         => TontineStatus::class,
        ];
    }

    // Relations

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function participants(): HasMany
    {
        return $this->hasMany(TontineParticipant::class);
    }

    public function activeParticipants(): HasMany
    {
        return $this->hasMany(TontineParticipant::class)
            ->where('status', \App\Enums\ParticipantStatus::Active);
    }

    public function contributions(): HasManyThrough
    {
        return $this->hasManyThrough(Contribution::class, TontineParticipant::class);
    }

    // Scopes

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', TontineStatus::Active);
    }

    public function scopeClosed(Builder $query): Builder
    {
        return $query->where('status', TontineStatus::Closed);
    }

    public function scopeForOrganization(Builder $query, int $organizationId): Builder
    {
        return $query->where('organization_id', $organizationId);
    }

    public function scopeByFrequency(Builder $query, TontineFrequency $frequency): Builder
    {
        return $query->where('frequency', $frequency);
    }

    // Helpers

    public function isActive(): bool
    {
        return $this->status === TontineStatus::Active;
    }

    public function getFrequencyLabelAttribute(): string
    {
        return $this->frequency?->label() ?? $this->getRawOriginal('frequency');
    }
}
