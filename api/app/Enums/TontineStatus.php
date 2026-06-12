<?php

namespace App\Enums;

enum TontineStatus: string
{
    case Active   = 'active';
    case Closed   = 'closed';
    case Archived = 'archived';

    public function label(): string
    {
        return match($this) {
            self::Active   => 'Active',
            self::Closed   => 'Clôturée',
            self::Archived => 'Archivée',
        };
    }
}
