<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class RoleUserSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'name' => 'Administrador',
            'email' => 'admin@jjvv.cl',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        User::create([
            'name' => 'Presidente',
            'email' => 'presidente@jjvv.cl',
            'password' => Hash::make('password'),
            'role' => 'presidente',
        ]);

        User::create([
            'name' => 'Secretario',
            'email' => 'secretario@jjvv.cl',
            'password' => Hash::make('password'),
            'role' => 'secretario',
        ]);

        User::create([
            'name' => 'Tesorero',
            'email' => 'tesorero@jjvv.cl',
            'password' => Hash::make('password'),
            'role' => 'tesorero',
        ]);

        User::create([
            'name' => 'Vecino de Prueba',
            'email' => 'vecino@jjvv.cl',
            'password' => Hash::make('password'),
            'role' => 'vecino',
        ]);
    }
}