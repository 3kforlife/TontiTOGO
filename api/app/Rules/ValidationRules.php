<?php

namespace App\Rules;

class ValidationRules
{
    public const REGEX_NAME = '/^[\pL\s\'\-]+$/u';

    public const REGEX_ADDRESS = '/^[\pL\d\s\-\,\.\/]+$/u';

    public static function name(bool $required = true): array
    {
        return [
            $required ? 'required' : 'sometimes',
            'string',
            'min:3',
            'max:50',
            'regex:' . self::REGEX_NAME,
        ];
    }

    public static function organizationName(bool $required = true): array
    {
        return [
            $required ? 'required' : 'sometimes',
            'string',
            'min:3',
            'max:50',
            'regex:' . self::REGEX_NAME,
        ];
    }

    public static function tontineName(bool $required = true): array
    {
        return self::organizationName($required);
    }

    public static function address(bool $nullable = true): array
    {
        return [
            $nullable ? 'nullable' : 'required',
            'string',
            'min:4',
            'max:150',
            'regex:' . self::REGEX_ADDRESS,
        ];
    }

    /**
     * Règles pour un numéro de carnet (notebook_number).
     * Min 3, max 50 caractères. Lettres, chiffres, espaces, tirets.
     *
     * @param  string|null  $exceptId  ID à exclure pour la règle unique (update)
     */
    public static function notebookNumber(?string $exceptId = null, bool $required = true): array
    {
        $unique = $exceptId
            ? "unique:members,notebook_number,{$exceptId}"
            : 'unique:members,notebook_number';

        return [
            $required ? 'required' : 'sometimes',
            'string',
            'min:3',
            'max:50',
            'regex:' . self::REGEX_ADDRESS,
            $unique,
        ];
    }

    public static function notes(): array
    {
        return [
            'nullable',
            'string',
            'min:3',
            'max:500',
        ];
    }
}
