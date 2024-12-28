<?php

namespace Database\Seeders;

use App\Http\Controllers\CuotasController;
use App\Models\CuotasEstados;
use App\Models\Persona;
use App\Models\PersonaCargo;
use App\Models\PersonaEstado;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\UserRole;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {

        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('123456'),
        ]);

        Persona::create([
            'idUsuario' => 1,
            'idRole' => 1,
            'idCargo' => 1,
            'idEstado' => 1,
            'Rut' => '15967365-0',
            'Activo' => 1
        ]);

        User::factory(20)->create();

        CuotasEstados::create(['Estado'=>'Pendiente']);
        CuotasEstados::create(['Estado'=>'Aprobado']);
        CuotasEstados::create(['Estado'=>'Rechazado']);
        CuotasEstados::create(['Estado'=>'Cancelado']);

        UserRole::firstOrCreate(['Rol'=>'Administrador']);
        PersonaEstado::create([
            'Estado'=>'Activo',
        ]);

        PersonaCargo::create([
            'Cargo' => 'Administrador',
        ]);

        $controlador = new CuotasController();
        $controlador->sincronizarUserPersona();
        $controlador->sincronizarCuotas();
    }
}
