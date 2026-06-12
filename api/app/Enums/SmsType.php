<?php

namespace App\Enums;

enum SmsType: string
{
    case Confirmation = 'confirmation';
    case Reminder     = 'reminder';

    public function label(): string
    {
        return match($this) {
            self::Confirmation => 'Confirmation',
            self::Reminder     => 'Rappel',
        };
    }
}
