<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'test',
            'email' => 'test@gmail.com',
            'password' => bcrypt('12345678'),
        ]);

        // $this->call(RolesPermisosSeeder::class);

        $this->call([
            RolesPermisosSeeder::class,
            SucursalesSeeder::class,
            CategoriasSeeder::class,
            MedicamentosSeeder::class,
            MedicamentoSucursalSeeder::class,
            LotesSeeder::class,
        ]);
    }
}
