<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => $this->faker->randomElement(['depot', 'retrait', 'virement', 'paiement']),
            'montant' => $this->faker->randomFloat(2, 100, 50000), // Entre 100 et 50k FCFA
            'description' => $this->faker->sentence(),
            'date_transaction' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'statut' => $this->faker->randomElement(['reussi', 'en_cours', 'echoue']),
            'compte_id' => null, // Sera défini dans le seeder
        ];
    }
}
