<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;


class TogoPhone implements ValidationRule
{
    
    private const VALID_PREFIXES = [
        // Moov Money
        '79', '96', '97', '98', '99',
        // Yas Togo
        '70', '71', '72', '73', '90', '91', '92', '93'
    ];

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $local = self::toLocalFormat($value);

        if ($local === null) {
            $fail('Le numéro de téléphone doit être un numéro togolais valide.');
            return;
        }

        $prefix = substr($local, 0, 2);

        if (! in_array($prefix, self::VALID_PREFIXES, true)) {
            $fail("Le numéro de téléphone ne correspond à aucun opérateur togolais reconnu (Moov ou Yas Togo). Préfixe reçu : {$prefix}.");
        }
    }

   
    public static function normalize(string $value): ?string
    {
        $local = self::toLocalFormat($value);

        if ($local === null) {
            return null;
        }

        return '228' . $local;
    }

    // -------------------------------------------------------
    // Helpers privés
    // -------------------------------------------------------

    
    private static function toLocalFormat(string $value): ?string
    {

        $digits = preg_replace('/[\s\-\.\(\)\+]/', '', $value);

        $digits = preg_replace('/\D/', '', $digits);

        return match (true) {
            str_starts_with($digits, '228') && strlen($digits) === 11
                => substr($digits, 3),

            strlen($digits) === 8
                => $digits,

            default => null,
        };
    }
}
