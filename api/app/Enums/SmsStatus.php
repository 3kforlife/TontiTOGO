<?php

namespace App\Enums;

enum SmsStatus: string
{
    case Sent   = 'sent';
    case Failed = 'failed';

    public function label(): string
    {
        return match($this) {
            self::Sent   => 'Envoyé',
            self::Failed => 'Échoué',
        };
    }
}
