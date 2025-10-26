<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TelephoneRule implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * Valide un numéro de téléphone sénégalais
     * Formats acceptés :
     * - +221771234567 (format international)
     * - 221771234567 (sans +)
     * - 771234567 (format local)
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        // Nettoyer le numéro (supprimer espaces et tirets)
        $cleanNumber = preg_replace('/[\s\-]/', '', $value);

        // Vérifier que c'est numérique après nettoyage
        if (!is_numeric($cleanNumber)) {
            $fail('Le numéro de téléphone doit contenir uniquement des chiffres.');
            return;
        }

        // Formats acceptés pour le Sénégal
        $patterns = [
            '/^\+2217[05678]\d{7}$/',  // +221 7x xxx xxxx (Orange, Free, Expresso)
            '/^2217[05678]\d{7}$/',    // 221 7x xxx xxxx (sans +)
            '/^7[05678]\d{7}$/',       // 7x xxx xxxx (format local)
        ];

        $isValid = false;
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $cleanNumber)) {
                $isValid = true;
                break;
            }
        }

        if (!$isValid) {
            $fail('Le numéro de téléphone doit être un numéro sénégalais valide (Orange, Free ou Expresso).');
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
        return 'Le numéro de téléphone fourni n\'est pas valide.';
    }
}
