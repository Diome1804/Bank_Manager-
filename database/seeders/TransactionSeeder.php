<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Transaction;
use App\Models\Compte;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $comptes = Compte::all();

        foreach ($comptes as $compte) {
            // Créer quelques transactions par compte
            Transaction::factory()->count(rand(2, 5))->create([
                'compte_id' => $compte->id,
            ]);
        }
    }
}
