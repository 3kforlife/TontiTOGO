<?php

namespace App\Models;

use App\Enums\SmsStatus;
use App\Enums\SmsType;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SmsLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'recipient',
        'message',
        'type',
        'status',
        'response_payload',
    ];

    protected function casts(): array
    {
        return [
            'response_payload' => 'array',
            'type'             => SmsType::class,
            'status'           => SmsStatus::class,
        ];
    }

    // Relations

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    // Scopes

    public function scopeSent(Builder $query): Builder
    {
        return $query->where('status', SmsStatus::Sent->value);
    }

    public function scopeFailed(Builder $query): Builder
    {
        return $query->where('status', SmsStatus::Failed->value);
    }

    public function scopeConfirmations(Builder $query): Builder
    {
        return $query->where('type', SmsType::Confirmation->value);
    }

    public function scopeReminders(Builder $query): Builder
    {
        return $query->where('type', SmsType::Reminder->value);
    }

    public function scopeForOrganization(Builder $query, int $organizationId): Builder
    {
        return $query->where('organization_id', $organizationId);
    }

    // Helpers

    public function isSent(): bool
    {
        return $this->status === SmsStatus::Sent;
    }

    public function isFailed(): bool
    {
        return $this->status === SmsStatus::Failed;
    }
}
