<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Setting extends Model
{
    use HasFactory;

    protected $fillable = [
        'organization_id',
        'key',
        'value',
    ];

    // Relations

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    // Helpers

    public static function get(int $organizationId, string $key, mixed $default = null): mixed
    {
        $setting = static::where('organization_id', $organizationId)
            ->where('key', $key)
            ->first();

        return $setting?->value ?? $default;
    }

    
    public static function set(int $organizationId, string $key, mixed $value): static
    {
        return static::updateOrCreate(
            ['organization_id' => $organizationId, 'key' => $key],
            ['value' => $value]
        );
    }
}
