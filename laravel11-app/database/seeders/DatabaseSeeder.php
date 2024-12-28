<?php

namespace Database\Seeders;

use App\Models\CuotasEstados;
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

        /*User::factory()->create([
            'name' => 'Admin',
            'email' => 'admin@example.com',
        ]);*/

/*        CuotasEstados::create(['Estado'=>'Pendiente']);
        CuotasEstados::create(['Estado'=>'Aprobado']);
        CuotasEstados::create(['Estado'=>'Rechazado']);
        CuotasEstados::create(['Estado'=>'Cancelado']);*/

        User::factory(20)->create();
    }
}
