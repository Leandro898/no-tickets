<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
        ]);

        // Usuario de prueba
        \App\Models\User::factory()->create([
            'name' => 'pruebas',
            'email' => 'pruebas@mail.com',
        ]);
    }
}
