<?php

namespace App\Http\Requests;

use App\Rules\TelephoneRule;
use App\Rules\NciRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Pour le moment, pas d'authentification
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $clientId = $this->route('compte')?->client_id;

        return [
            'titulaire' => 'nullable|string|min:2|max:255',
            'informationsClient' => 'nullable|array',
            'informationsClient.nom' => 'nullable|string|min:2|max:255',
            'informationsClient.prenom' => 'nullable|string|min:2|max:255',
            'informationsClient.email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('clients', 'email')->ignore($clientId)
            ],
            'informationsClient.telephone' => [
                'nullable',
                new TelephoneRule(),
                Rule::unique('clients', 'telephone')->ignore($clientId)
            ],
            'informationsClient.nci' => [
                'nullable',
                new NciRule(),
                Rule::unique('clients', 'nci')->ignore($clientId)
            ],
            'informationsClient.adresse' => 'nullable|string|min:5|max:500',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'titulaire.string' => 'Le titulaire doit être une chaîne de caractères.',
            'titulaire.min' => 'Le titulaire doit contenir au moins :min caractères.',
            'titulaire.max' => 'Le titulaire ne peut pas dépasser :max caractères.',
            'informationsClient.nom.string' => 'Le nom doit être une chaîne de caractères.',
            'informationsClient.nom.min' => 'Le nom doit contenir au moins :min caractères.',
            'informationsClient.nom.max' => 'Le nom ne peut pas dépasser :max caractères.',
            'informationsClient.prenom.string' => 'Le prénom doit être une chaîne de caractères.',
            'informationsClient.prenom.min' => 'Le prénom doit contenir au moins :min caractères.',
            'informationsClient.prenom.max' => 'Le prénom ne peut pas dépasser :max caractères.',
            'informationsClient.email.email' => 'L\'adresse email n\'est pas valide.',
            'informationsClient.email.unique' => 'Cette adresse email est déjà utilisée.',
            'informationsClient.email.max' => 'L\'email ne peut pas dépasser :max caractères.',
            'informationsClient.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'informationsClient.nci.unique' => 'Ce numéro NCI est déjà utilisé.',
            'informationsClient.adresse.string' => 'L\'adresse doit être une chaîne de caractères.',
            'informationsClient.adresse.min' => 'L\'adresse doit contenir au moins :min caractères.',
            'informationsClient.adresse.max' => 'L\'adresse ne peut pas dépasser :max caractères.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Vérifier qu'au moins un champ est fourni
            $data = $this->all();
            $hasTitulaire = !empty($data['titulaire']);
            $hasClientInfo = !empty($data['informationsClient']) &&
                           collect($data['informationsClient'])->filter()->isNotEmpty();

            if (!$hasTitulaire && !$hasClientInfo) {
                $validator->errors()->add('general', 'Au moins un champ de modification doit être fourni.');
            }
        });
    }
}
