<?php

namespace App\Http\Requests;

use App\Rules\NciRule;
use App\Rules\TelephoneRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateCompteRequest extends FormRequest
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
            // Validation du compte
            'type' => ['required', 'string', Rule::in(['courant', 'epargne', 'cheque'])],
            'soldeInitial' => ['required', 'numeric', 'min:10000'],
            'devise' => ['required', 'string', 'in:FCFA,XOF'],

            // Validation du client
            'client' => ['required', 'array'],
            'client.nom' => ['required_if:client.id,null', 'string', 'min:2', 'max:255'],
            'client.prenom' => ['required_if:client.id,null', 'string', 'min:2', 'max:255'],
            'client.email' => [
                'required_if:client.id,null',
                'email',
                'max:255',
                Rule::unique('clients', 'email')->ignore($this->input('client.id'))
            ],
            'client.telephone' => [
                'required_if:client.id,null',
                'string',
                new TelephoneRule(),
                Rule::unique('clients', 'telephone')->ignore($this->input('client.id'))
            ],
            'client.nci' => [
                'required_if:client.id,null',
                'string',
                new NciRule(),
                Rule::unique('clients', 'nci')->ignore($this->input('client.id'))
            ],
            'client.adresse' => ['required_if:client.id,null', 'string', 'min:5', 'max:500'],
            'client.id' => ['nullable', 'uuid', Rule::exists('clients', 'id')->where(function ($query) {
                return $query->whereNull('deleted_at');
            })],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'type.required' => 'Le type de compte est obligatoire.',
            'type.in' => 'Le type doit être : courant, epargne ou cheque.',
            'soldeInitial.required' => 'Le solde initial est obligatoire.',
            'soldeInitial.numeric' => 'Le solde initial doit être un nombre.',
            'soldeInitial.min' => 'Le solde initial doit être d\'au moins 10 000 FCFA.',
            'devise.required' => 'La devise est obligatoire.',
            'devise.in' => 'La devise doit être FCFA ou XOF.',
            'client.required' => 'Les informations du client sont obligatoires.',
            'client.nom.required_if' => 'Le nom du client est obligatoire.',
            'client.prenom.required_if' => 'Le prénom du client est obligatoire.',
            'client.email.required_if' => 'L\'email du client est obligatoire.',
            'client.email.unique' => 'Cet email est déjà utilisé.',
            'client.telephone.required_if' => 'Le téléphone du client est obligatoire.',
            'client.telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'client.nci.required_if' => 'Le numéro NCI du client est obligatoire.',
            'client.nci.unique' => 'Ce numéro NCI est déjà utilisé.',
            'client.adresse.required_if' => 'L\'adresse du client est obligatoire.',
            'client.id.exists' => 'Le client spécifié n\'existe pas.',
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

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $clientData = $this->input('client', []);

            // Validation personnalisée pour le client
            if (!empty($clientData['id'])) {
                // Si un ID client est fourni, vérifier qu'il existe et n'est pas supprimé
                $clientExists = \App\Models\Client::where('id', $clientData['id'])
                    ->whereNull('deleted_at')
                    ->exists();

                if (!$clientExists) {
                    $validator->errors()->add('client.id', 'Le client spécifié n\'existe pas.');
                }
            } elseif (empty($clientData['id']) && !isset($clientData['nom'])) {
                // Si ni ID ni nom fourni, c'est une erreur
                $validator->errors()->add('client', 'Vous devez soit fournir un ID de client existant, soit les informations pour créer un nouveau client.');
            }
        });
    }
}
