<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des clients de test avec des credentials pour l'authentification
        $clients = [
            [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'nom' => 'Dupont',
                'prenom' => 'Jean',
                'email' => 'client1@test.com',
                'telephone' => '771234567',
                'nci' => '1234567890123',
                'adresse' => 'Dakar, Sénégal',
                'type_user' => 'client',
                'password_temp' => Hash::make('password123'),
                'code_verification' => '123456',
            ],
            [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'nom' => 'Diallo',
                'prenom' => 'Marie',
                'email' => 'client2@test.com',
                'telephone' => '772345678',
                'nci' => '1234567890124',
                'adresse' => 'Thiès, Sénégal',
                'type_user' => 'client',
                'password_temp' => Hash::make('password123'),
                'code_verification' => '234567',
            ],
        ];

        foreach ($clients as $clientData) {
            Client::create($clientData);
        }

        // Créer 8 clients supplémentaires avec factory
        Client::factory(8)->create();
    }
}
