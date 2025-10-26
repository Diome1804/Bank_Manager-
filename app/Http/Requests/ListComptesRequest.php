<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListComptesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Accessible publiquement pour le moment
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'page' => ['nullable', 'integer', 'min:1'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:100'],
            'type' => ['nullable', 'string', Rule::in(['courant', 'epargne', 'cheque'])],
            'statut' => ['nullable', 'string', Rule::in(['actif', 'bloque', 'ferme'])],
            'search' => ['nullable', 'string', 'min:1', 'max:255'],
            'sort' => ['nullable', 'string', Rule::in(['dateCreation', 'solde', 'titulaire'])],
            'order' => ['nullable', 'string', Rule::in(['asc', 'desc'])],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'limit.max' => 'Le nombre maximum d\'éléments par page est de 100.',
            'type.in' => 'Le type doit être : courant, epargne ou cheque.',
            'statut.in' => 'Le statut doit être : actif, bloque ou ferme.',
            'sort.in' => 'Le tri doit être : dateCreation, solde ou titulaire.',
            'order.in' => 'L\'ordre doit être : asc ou desc.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convertir 'cheque' en 'courant' pour la validation
        if ($this->type === 'cheque') {
            $this->merge(['type' => 'courant']);
        }
    }
}
