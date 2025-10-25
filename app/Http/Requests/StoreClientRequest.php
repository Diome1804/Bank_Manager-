<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreClientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // TODO: Add proper authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'telephone' => 'required|string|max:20|unique:users',
            'adresse' => 'required|string|max:500',
            'date_naissance' => 'required|date|before:today',
            'numero_cni' => 'required|string|max:20|unique:users',
            'profession' => 'nullable|string|max:255',
            'salaire_mensuel' => 'nullable|numeric|min:0',
            'employeur' => 'nullable|string|max:255',
            'statut_emploi' => 'nullable|in:salarie,independant,retraite,sans_emploi',
            'documents.*' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:5120', // 5MB max
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Le nom est obligatoire.',
            'email.required' => 'L\'email est obligatoire.',
            'email.email' => 'L\'email doit être valide.',
            'email.unique' => 'Cet email est déjà utilisé.',
            'password.required' => 'Le mot de passe est obligatoire.',
            'password.min' => 'Le mot de passe doit contenir au moins 8 caractères.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'telephone.required' => 'Le numéro de téléphone est obligatoire.',
            'telephone.unique' => 'Ce numéro de téléphone est déjà utilisé.',
            'adresse.required' => 'L\'adresse est obligatoire.',
            'date_naissance.required' => 'La date de naissance est obligatoire.',
            'date_naissance.before' => 'La date de naissance doit être antérieure à aujourd\'hui.',
            'numero_cni.required' => 'Le numéro CNI est obligatoire.',
            'numero_cni.unique' => 'Ce numéro CNI est déjà utilisé.',
            'salaire_mensuel.numeric' => 'Le salaire mensuel doit être un nombre.',
            'salaire_mensuel.min' => 'Le salaire mensuel doit être positif.',
            'statut_emploi.in' => 'Le statut d\'emploi n\'est pas valide.',
            'documents.*.mimes' => 'Les documents doivent être des fichiers PDF ou images.',
            'documents.*.max' => 'Chaque document ne doit pas dépasser 5MB.',
        ];
    }
}
