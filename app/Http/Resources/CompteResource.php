<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompteResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'numeroCompte' => $this->numero_compte,
            'titulaire' => $this->client->nom . ' ' . $this->client->prenom,
            'type' => $this->type_compte,
            'solde' => $this->solde,
            'devise' => 'FCFA', // Valeur par défaut selon la spec
            'dateCreation' => $this->date_ouverture->toISOString(),
            'statut' => $this->statut,
            'motifBlocage' => $this->statut === 'bloque' ? 'Inactivité de 30+ jours' : null,
            'metadata' => [
                'derniereModification' => $this->updated_at->toISOString(),
                'version' => 1
            ]
        ];
    }
}
