<?php

namespace App\Enums;

enum SettlementStatus: string
{
    case Validated   = 'validated';
    case Discrepancy = 'discrepancy';

    public function label(): string
    {
        return match($this) {
            self::Validated   => 'Validé',
            self::Discrepancy => 'Écart détecté',
        };
    }
}
