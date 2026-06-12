<?php

namespace App\Enums;

enum UserRole: string
{
    case Responsible = 'responsible';
    case Agent       = 'agent';

    public function label(): string
    {
        return match($this) {
            self::Responsible => 'Responsable',
            self::Agent       => 'Agent',
        };
    }

    public function isAgent(): bool
    {
        return $this === self::Agent;
    }

    public function isResponsible(): bool
    {
        return $this === self::Responsible;
    }
}
