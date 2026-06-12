<?php

namespace App\Enums;

enum ContributionSettlementStatus: string
{
    case Pending  = 'pending';
    case Settled  = 'settled';

    public function label(): string
    {
        return match($this) {
            self::Pending => 'En attente',
            self::Settled => 'Versé',
        };
    }
}
