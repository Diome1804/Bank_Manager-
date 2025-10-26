<?php

namespace App\Observers;

use App\Models\Compte;
use App\Models\Client;
use Illuminate\Support\Str;

class CompteObserver
{
    /**
     * Handle the Compte "creating" event.
     * Vérifie/crée le client et prépare les données du compte
     */
    public function creating(Compte $compte): void
    {
        $clientData = request()->input('client', []);

        // Si un ID client est fourni, vérifier qu'il existe
        if (!empty($clientData['id'])) {
            $client = Client::find($clientData['id']);
            if (!$client) {
                throw new \Exception('Client spécifié non trouvé');
            }
            $compte->client_id = $client->id;
        } else {
            // Créer un nouveau client
            $client = $this->createClient($clientData);
            $compte->client_id = $client->id;
        }

        // Générer le numéro de compte unique
        $compte->numero_compte = $this->generateNumeroCompte();

        // Définir le solde initial (sera calculé plus tard avec les transactions)
        $compte->solde = request()->input('soldeInitial', 0);

        // Définir le statut par défaut
        $compte->statut = 'actif';
    }

    /**
     * Handle the Compte "created" event.
     * Prépare les notifications (SMS + Email) - Phase ultérieure
     */
    public function created(Compte $compte): void
    {
        // TODO: Phase ultérieure - Envoyer notifications
        // - Générer mot de passe temporaire
        // - Générer code de vérification
        // - Envoyer email avec mot de passe
        // - Envoyer SMS avec code

        // Pour l'instant, on log simplement la création
        logger()->info('Nouveau compte créé', [
            'compte_id' => $compte->id,
            'numero_compte' => $compte->numero_compte,
            'client_id' => $compte->client_id,
            'solde_initial' => $compte->solde,
        ]);
    }

    /**
     * Créer un nouveau client avec génération de mot de passe et code
     */
    private function createClient(array $clientData): Client
    {
        // Générer un mot de passe temporaire
        $password = Str::random(12);

        // Générer un code de vérification (6 chiffres)
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $client = new Client([
            'id' => (string) Str::uuid(),
            'nom' => $clientData['nom'],
            'prenom' => $clientData['prenom'],
            'email' => $clientData['email'],
            'telephone' => $clientData['telephone'],
            'nci' => $clientData['nci'],
            'adresse' => $clientData['adresse'],
            'password_temp' => bcrypt($password), // Stockage temporaire
            'code_verification' => $code,
            'type_user' => 'client',
        ]);

        $client->save();

        // TODO: Phase ultérieure - Envoyer notifications
        logger()->info('Nouveau client créé', [
            'client_id' => $client->id,
            'email' => $client->email,
            'telephone' => $client->telephone,
            'password_temp_generated' => true,
            'code_generated' => $code,
        ]);

        return $client;
    }

    /**
     * Générer un numéro de compte unique
     * Format: SN + timestamp + 4 chiffres aléatoires
     */
    private function generateNumeroCompte(): string
    {
        do {
            $numero = 'SN' . now()->format('ymdHis') . random_int(1000, 9999);
        } while (Compte::where('numero_compte', $numero)->exists());

        return $numero;
    }

    /**
     * Handle the Compte "updating" event.
     */
    public function updating(Compte $compte): void
    {
        // Validation des mises à jour si nécessaire
    }

    /**
     * Handle the Compte "deleted" event.
     */
    public function deleted(Compte $compte): void
    {
        // Log de suppression
        logger()->info('Compte supprimé', [
            'compte_id' => $compte->id,
            'numero_compte' => $compte->numero_compte,
        ]);
    }

    /**
     * Handle the Compte "restored" event.
     */
    public function restored(Compte $compte): void
    {
        // Log de restauration
        logger()->info('Compte restauré', [
            'compte_id' => $compte->id,
            'numero_compte' => $compte->numero_compte,
        ]);
    }

    /**
     * Handle the Compte "force deleted" event.
     */
    public function forceDeleted(Compte $compte): void
    {
        // Log de suppression définitive
        logger()->warning('Compte supprimé définitivement', [
            'compte_id' => $compte->id,
            'numero_compte' => $compte->numero_compte,
        ]);
    }
}
