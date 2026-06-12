<?php

namespace App\Enums;

enum ParticipantStatus: string
{
    case Active    = 'active';
    case Cancelled = 'cancelled';
    case Suspended = 'suspended';

    public function label(): string
    {
        return match($this) {
            self::Active    => 'Actif',
            self::Cancelled => 'Annulé',
            self::Suspended => 'Suspendu',
        };
    }
}
