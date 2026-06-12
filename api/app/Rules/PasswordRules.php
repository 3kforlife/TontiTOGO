<?php

namespace App\Rules;

use Illuminate\Validation\Rules\Password;

/**
 * Politique pour mot de passe :
 *   ✓ Minimum 8 caractères
 *   ✓ Au moins 1 lettre majuscule (A-Z)
 *   ✓ Au moins 1 lettre minuscule (a-z)
 *   ✓ Au moins 1 chiffre (0-9)
 *   ✓ Au moins 1 caractère spécial parmi : @ $ ! % * # ? & _ - + = ^ ~
 
 */
class PasswordRules
{
    private static function policy(): Password
    {
        return Password::min(8)
            ->mixedCase()  
            ->numbers()    
            ->symbols();   
    }

    /**
     * Règles pour la création d'un mot de passe.
     * (inscription responsable, création agent, reset OTP/email)
     *
     * @return array<int, mixed>
     */
    public static function creation(): array
    {
        return [
            'required',
            'string',
            'confirmed',
            static::policy(),
        ];
    }

    /**
     * Règles pour la modification d'un mot de passe existant.
     * (changement depuis le profil, premier changement agent)
     *
     * @return array<int, mixed>
     */
    public static function update(): array
    {
        return [
            'required',
            'string',
            'confirmed',
            static::policy(),
        ];
    }
}
