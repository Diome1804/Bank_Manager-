<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Admin;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer des admins de test avec des credentials pour l'authentification
        $admins = [
            [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'nom' => 'Admin',
                'prenom' => 'Super',
                'email' => 'admin@test.com',
                'telephone' => '701234567',
                'password_temp' => bcrypt('admin123'),
                'role' => 'super_admin',
                'type_user' => 'admin',
            ],
            [
                'id' => (string) \Illuminate\Support\Str::uuid(),
                'nom' => 'Manager',
                'prenom' => 'Banque',
                'email' => 'manager@test.com',
                'telephone' => '702345678',
                'password_temp' => bcrypt('manager123'),
                'role' => 'manager',
                'type_user' => 'admin',
            ],
        ];

        foreach ($admins as $adminData) {
            Admin::create($adminData);
        }

        // Créer 3 admins supplémentaires avec factory
        Admin::factory(3)->create();
    }
}
