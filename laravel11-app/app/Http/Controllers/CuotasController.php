<?php

namespace App\Http\Controllers;

use App\Models\Cuota;
use App\Models\Persona;
use App\Models\User;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CuotasController extends Controller
{

    public function sincronizarCuotas($monto = 20000)
    {

        // Trae las personas activas
        $personas = Persona::where('Activo', 1)
            ->orderBy('FechaReclutamiento', 'asc')
            ->get();


        foreach ($personas as $persona) {
            $fechaIngreso = Carbon::parse($persona->FechaReclutamiento);
            $fechaHoy = Carbon::now();
            $fechaFinAnio = Carbon::now()->endOfYear();

            // Trae ultima cuota creada
            $cuota = Cuota::select('FechaPeriodo')
                ->where('idUser', $persona->idUsuario)
                ->orderBy('FechaPeriodo', 'desc')
                ->first();

            if ($cuota) {
                $ultimaFechaCuota = Carbon::parse($cuota->FechaPeriodo);
            } else {
                $ultimaFechaCuota = $fechaIngreso;
            }

            if($ultimaFechaCuota < $fechaFinAnio) {

//                print($persona->idUsuario);
                $diffMeses = round($ultimaFechaCuota->diffInMonths($fechaFinAnio));
//                dump($diffMeses);
                for ($i = 0; $i <= $diffMeses; $i++) {
                    $fechaPeriodo = $ultimaFechaCuota->copy()->addMonths($i);
                    $fechaVencimiento = $fechaPeriodo->copy()->addMonths(1);
//                    print($fechaPeriodo->format("Y-m-d"). " ". $fechaVencimiento->format("Y-m-d"));

                    $cuota = Cuota::create([
                        'idUser' => $persona->idUsuario,
                        'FechaPeriodo' => $fechaPeriodo->format('Y-m-01'),
                        'FechaVencimiento' => $fechaVencimiento->format('Y-m-05'),
                        'Estado' => 1,
                        'Monto' => $monto,
                        'Pendiente' => $monto,
                        'Recaudado' => 0,
                    ]);
                }
            }
        }
    }

    public function sincronizarUserPersona(){
        $users = User::all();
        foreach ($users as $user) {
            if($user->persona){
                dump($user->persona);
            }else{
                $nuevo = Persona::create([
                    'idUsuario' => $user->id,
                    'idCargo' => 1,
                    'idEstado' => 1,
                    'FechaReclutamiento' => Carbon::today()->subDays(rand(0,350))->format('Y-m-d'),
                    'Rut' => rand(11111111, 99999999) . "-". rand(1,9),
                    'Activo' => 1,
                ]);
                dump($nuevo);
            }
        }
    }

}
