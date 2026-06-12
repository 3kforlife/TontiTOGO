<?php

namespace App\Helpers;

/**
 * Helper de sanitisation des numéros de téléphone togolais.
 */
class PhoneFormatter
{
    /**
     * Normalise un numéro togolais au format strict 228XXXXXXXX (11 chiffres).
     * C'est le format exigé par l'API Termii pour le routage SMS.
     */
    public static function normalize(string $phone): string
    {
        // Supprimer tout caractère non numérique
        $digits = preg_replace('/\D/', '', $phone);

        // Déjà au bon format
        if (str_starts_with($digits, '228') && strlen($digits) === 11) {
            return $digits;
        }

        // Format local 8 chiffres → ajouter l'indicatif Togo
        return '228' . $digits;
    }
}
