<?php

namespace App\Services;

use App\Models\Compte;

class CompteDeletionService
{
    /**
     * Supprime un compte (soft delete)
     */
    public function delete(Compte $compte): array
    {
        $this->validateDeletion($compte);

        $this->prepareForDeletion($compte);

        $compte->delete();

        return $this->getDeletionResponse($compte);
    }

    /**
     * Valide si le compte peut être supprimé
     */
    private function validateDeletion(Compte $compte): void
    {
        if ($compte->trashed()) {
            throw new \Exception('Ce compte a déjà été supprimé');
        }

        // TODO: Ajouter vérification de solde positif dans une future version
        // if ($compte->solde > 0) {
        //     throw new \Exception('Impossible de supprimer un compte avec un solde positif');
        // }
    }

    /**
     * Prépare le compte pour la suppression
     */
    private function prepareForDeletion(Compte $compte): void
    {
        $compte->update([
            'statut' => 'ferme',
            'date_fermeture' => now(),
        ]);
    }

    /**
     * Retourne les données de réponse pour la suppression
     */
    private function getDeletionResponse(Compte $compte): array
    {
        return [
            'id' => $compte->id,
            'numeroCompte' => $compte->numero_compte,
            'statut' => 'ferme',
            'dateFermeture' => $compte->date_fermeture ? $compte->date_fermeture->toISOString() : now()->toISOString(),
        ];
    }
}