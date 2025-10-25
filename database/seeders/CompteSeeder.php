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
        $clients = \App\Models\Client::all();

        foreach ($clients as $client) {
            // Créer 1 à 3 comptes par client
            \App\Models\Compte::factory()->count(rand(1, 3))->create([
                'client_id' => $client->id,
            ]);
        }
    }
}
