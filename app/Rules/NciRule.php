<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NciRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * Valide un numéro NCI sénégalais
     * Format attendu : 13 chiffres commençant par 1 ou 2
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Vérifier que c'est une chaîne numérique
        if (!is_string($value) || !is_numeric($value)) {
            $fail('Le numéro NCI doit être composé uniquement de chiffres.');
            return;
        }

        // Vérifier la longueur (13 chiffres pour le Sénégal)
        if (strlen($value) !== 13) {
            $fail('Le numéro NCI doit contenir exactement 13 chiffres.');
            return;
        }

        // Vérifier qu'il commence par 1 ou 2 (convention sénégalaise)
        if (!in_array($value[0], ['1', '2'])) {
            $fail('Le numéro NCI doit commencer par 1 ou 2.');
            return;
        }

        // Vérifier l'unicité (optionnel - peut être fait au niveau de la base)
        // Cette validation peut être ajoutée si nécessaire
    }

    /**
     * Get the validation error message.
     */
    public function message(): string
    {
        return 'Le numéro NCI fourni n\'est pas valide.';
    }
}
