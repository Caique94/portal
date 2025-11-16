<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name'      => 'Administrador',
            'email'     => 'admin@example.com',
            'data_nasc' => '1981-06-09',
            'papel'     => 'admin',
            'password'  => Hash::make('123')
        ]);

        User::factory()->create([
            'name'      => 'Financeiro',
            'email'     => 'fin@example.com',
            'data_nasc' => '2000-01-01',
            'papel'     => 'financeiro',
            'password'  => Hash::make('123')
        ]);

        User::factory()->create([
            'name'      => 'Consultor A',
            'email'     => 'cona@example.com',
            'data_nasc' => '1999-02-02',
            'papel'     => 'consultor',
            'password'  => Hash::make('123')
        ]);

        User::factory()->create([
            'name'      => 'Consultor B',
            'email'     => 'conb@example.com',
            'data_nasc' => '2001-03-03',
            'papel'     => 'consultor',
            'password'  => Hash::make('123')
        ]);


    }
}
