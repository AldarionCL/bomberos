<?php

namespace Database\Seeders;

use App\Http\Controllers\CuotasController;
use App\Models\CuotasEstados;
use App\Models\DocumentosTipo;
use App\Models\Persona;
use App\Models\PersonaCargo;
use App\Models\PersonaEstado;
use App\Models\SolicitudesTipo;
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

        $rol = UserRole::create([
            'Rol' => 'Administrador',
            'Descripcion' => 'Administrador del sistema',
        ]);
        UserRole::create([
            'Rol' => 'Usuario',
            'Descripcion' => 'Usuario del sistema',
        ]);

        User::create([
            'name' => 'Admin',
            'email' => 'admin@gmail.com',
            'password' => bcrypt('123456'),
            'idRole' => $rol->id
        ]);

        PersonaCargo::create([
            'Cargo' => 'Administrador',
            'Descripcion' => 'Administrador del sistema',
            'Activo' => 1,
        ]);

        PersonaEstado::create([
            'Estado' => 'Activo',
            'Descripcion' => 'Persona Activa',
        ]);
        PersonaEstado::create([
            'Estado' => 'Inactivo',
            'Descripcion' => 'Persona Inactiva',
        ]);
        PersonaEstado::create([
            'Estado' => 'Licencia',
            'Descripcion' => 'Persona con Licencia',
        ]);
        PersonaEstado::create([
            'Estado' => 'Baja',
            'Descripcion' => 'Persona dada Baja',
        ]);

        Persona::create([
            'idUsuario' => 1,
            'idCargo' => 1,
            'idEstado' => 1,
            'Rut' => '15967365-0',
            'Activo' => 1
        ]);

//        User::factory(20)->create();

        CuotasEstados::create(['Estado'=>'Pendiente']);
        CuotasEstados::create(['Estado'=>'Aprobado']);
        CuotasEstados::create(['Estado'=>'Rechazado']);
        CuotasEstados::create(['Estado'=>'Cancelado']);

        SolicitudesTipo::create([
            'Tipo' => 'Solicitud Baja',
            'Descripcion' => 'Solicitud de Baja del voluntario',
        ]);
        SolicitudesTipo::create([
            'Tipo' => 'Solicitud Ingreso',
            'Descripcion' => 'Solicitud de Ingreso de nuevo voluntario',
        ]);
        SolicitudesTipo::create([
            'Tipo' => 'Solicitud Licencia',
            'Descripcion' => 'Solicitud de Licencia o Permiso especial del voluntario',
        ]);


        PersonaCargo::create([
            'Cargo' => 'Director',
        ]);
        PersonaCargo::create([
            'Cargo' => 'Capitán',
        ]);
        PersonaCargo::create([
            'Cargo' => 'Teniente 1',
        ]);
        PersonaCargo::create([
            'Cargo' => 'Teniente 2',
        ]);
        PersonaCargo::create([
            'Cargo' => 'Teniente 3',
        ]);
        PersonaCargo::create([
            'Cargo' => 'Secretario',
        ]);
        PersonaCargo::create([
            'Cargo' => 'Tesorero',
        ]);
        PersonaCargo::create([
            'Cargo' => 'Ayudante',
        ]);
        PersonaCargo::create([
            'Cargo' => 'Consejero',
        ]);
        PersonaCargo::create([
            'Cargo' => 'Voluntario',
        ]);
        PersonaCargo::create([
            'Cargo' => 'Maquinista',
        ]);


        DocumentosTipo::create([
            'Tipo' => 'Orden del día',
        ]);
        DocumentosTipo::create([
            'Tipo' => 'Boletines',
        ]);
        DocumentosTipo::create([
            'Tipo' => 'Cuadro de Honor',
        ]);
        DocumentosTipo::create([
            'Tipo' => 'Cuotas',
        ]);
        DocumentosTipo::create([
            'Tipo' => 'Acta de sesiones',
        ]);
        DocumentosTipo::create([
            'Tipo' => 'Cedula Identidad',
            'Clasificacion' => 'privado',
        ]);
        DocumentosTipo::create([
            'Tipo' => 'Curriculum',
            'Clasificacion' => 'privado',
        ]);
        DocumentosTipo::create([
            'Tipo' => 'Chequeo Médico',
            'Clasificacion' => 'privado',
        ]);
        DocumentosTipo::create([
            'Tipo' => 'Comprobante Domicilio',
            'Clasificacion' => 'privado',
        ]);
        DocumentosTipo::create([
            'Tipo' => 'Otro',
            'Clasificacion' => 'privado',
        ]);


//        $controlador = new CuotasController();
//        $controlador->sincronizarUserPersona();
//        $controlador->sincronizarCuotas();
    }
}
