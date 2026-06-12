<?php

namespace App\Enums;

enum TontineFrequency: string
{
    case Daily   = 'daily';
    case Weekly  = 'weekly';
    case Monthly = 'monthly';

    public function label(): string
    {
        return match($this) {
            self::Daily   => 'Journalière',
            self::Weekly  => 'Hebdomadaire',
            self::Monthly => 'Mensuelle',
        };
    }
}
