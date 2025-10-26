<?php

namespace App\Services;

use App\Models\Compte;
use App\Models\Client;
use Illuminate\Support\Collection;

class CompteUpdateService
{
    /**
     * Met à jour les informations du client d'un compte
     */
    public function updateClient(Compte $compte, array $validatedData): Compte
    {
        $client = $this->getClient($compte);

        $updateData = $this->prepareUpdateData($validatedData);

        if (!empty($updateData)) {
            $client->update($updateData);
        }

        return $compte->fresh('client');
    }

    /**
     * Récupère le client associé au compte
     */
    private function getClient(Compte $compte): Client
    {
        $client = $compte->client;

        if (!$client) {
            throw new \Exception('Client non trouvé pour ce compte');
        }

        return $client;
    }

    /**
     * Prépare les données de mise à jour à partir des données validées
     */
    private function prepareUpdateData(array $validatedData): array
    {
        $updateData = [];

        // Gestion du titulaire (si fourni)
        if (isset($validatedData['titulaire'])) {
            // Pour le moment, on ne traite pas le titulaire directement
            // Cela pourrait être étendu pour parser nom + prénom
        }

        // Gestion des informations client
        if (isset($validatedData['informationsClient'])) {
            $clientData = $validatedData['informationsClient'];

            $this->addFieldIfPresent($updateData, $clientData, 'nom');
            $this->addFieldIfPresent($updateData, $clientData, 'prenom');
            $this->addFieldIfPresent($updateData, $clientData, 'email');
            $this->addFieldIfPresent($updateData, $clientData, 'telephone');
            $this->addFieldIfPresent($updateData, $clientData, 'nci');
            $this->addFieldIfPresent($updateData, $clientData, 'adresse');
        }

        return $updateData;
    }

    /**
     * Ajoute un champ aux données de mise à jour s'il est présent
     */
    private function addFieldIfPresent(array &$updateData, array $sourceData, string $field): void
    {
        if (isset($sourceData[$field])) {
            $updateData[$field] = $sourceData[$field];
        }
    }
}