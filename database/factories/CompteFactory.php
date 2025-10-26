<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Compte>
 */
class CompteFactory extends Factory
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
            'numero_compte' => $this->faker->unique()->numerify('SN##########'),
            'solde' => $this->faker->randomFloat(2, 1000, 1000000), // Entre 1000 et 1M FCFA
            'type_compte' => $this->faker->randomElement(['courant', 'epargne', 'entreprise']),
            'date_ouverture' => $this->faker->dateTimeBetween('-5 years', 'now'),
            'statut' => $this->faker->randomElement(['actif', 'bloque', 'ferme']),
            'client_id' => null, // Sera défini dans le seeder
        ];
    }
}
