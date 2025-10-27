<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CompteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des comptes pour les clients existants
        $clients = \App\Models\Client::all();

        foreach ($clients as $client) {
            // Créer 1-2 comptes par client
            $nombreComptes = rand(1, 2);

            for ($i = 0; $i < $nombreComptes; $i++) {
                \App\Models\Compte::factory()->create([
                    'client_id' => $client->id,
                ]);
            }
        }

        // S'assurer qu'on a au moins 20 comptes au total
        $comptesActuels = \App\Models\Compte::count();
        if ($comptesActuels < 20) {
            \App\Models\Compte::factory()->count(20 - $comptesActuels)->create();
        }
    }
}
