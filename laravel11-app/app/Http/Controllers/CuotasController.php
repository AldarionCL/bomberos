<?php

namespace App\Http\Controllers;

use App\Filament\Resources\PrecioCuotasResource;
use App\Models\Cuota;
use App\Models\Persona;
use App\Models\PrecioCuotas;
use App\Models\User;
use App\Models\UserRole;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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
            $fechaNacimiento = Carbon::parse($persona->FechaNacimiento);
            $edad = $fechaHoy->diffInYears($fechaNacimiento) * -1;
            $tipoVoluntario = $persona->TipoVoluntario ?? null;

            if ($edad < 50) {
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

                if ($ultimaFechaCuota < $fechaFinAnio) {

//                print($persona->idUsuario);
                    $diffMeses = round($ultimaFechaCuota->diffInMonths($fechaFinAnio));
//                dump($diffMeses);
                    for ($i = 0; $i <= $diffMeses; $i++) {
                        $fechaPeriodo = $ultimaFechaCuota->copy()->addMonths($i);
                        $fechaVencimiento = $fechaPeriodo->copy()->addMonths(1);
//                    print($fechaPeriodo->format("Y-m-d"). " ". $fechaVencimiento->format("Y-m-d"));

                        if ($tipoVoluntario == 'voluntario') {
                            $tipoCuota = 'cuota_ordinaria';
                        } else {
                            $tipoCuota = 'cuota_extraordinaria';
                        }

                        $cuotaMonto = PrecioCuotas::where('TipoVoluntario', $tipoVoluntario)
                            ->where('TipoCuota', $tipoCuota)
                            ->first();
                        $monto = $cuotaMonto->Monto ?? $monto;

                        if ($monto > 0) {
                            $cuota = Cuota::create([
                                'idUser' => $persona->idUsuario,
                                'FechaPeriodo' => $fechaPeriodo->format('Y-m-01'),
                                'FechaVencimiento' => $fechaVencimiento->format('Y-m-05'),
                                'Estado' => 1,
                                'Monto' => $monto,
                                'TipoCuota' => $tipoCuota,
                                'Pendiente' => $monto,
                                'Recaudado' => 0,
                            ]);
                        }
                    }
                }
            }
        }
    }

    public function sincronizarUserPersona()
    {
        $users = User::all();
        foreach ($users as $user) {
            if ($user->persona) {
                dump($user->persona);
            } else {
                $nuevo = Persona::create([
                    'idUsuario' => $user->id,
                    'idCargo' => 1,
                    'idEstado' => 1,
                    'FechaReclutamiento' => Carbon::today()->subDays(rand(0, 350))->format('Y-m-d'),
                    'Rut' => rand(11111111, 99999999) . "-" . rand(1, 9),
                    'Activo' => 1,
                ]);
                dump($nuevo);
            }
        }
    }

    public function revisaCuotasVencidas()
    {
        $cuotas = Cuota::where('Estado', 1)
            ->where('FechaVencimiento', '<', Carbon::now())
            ->get();

        foreach ($cuotas as $cuota) {
            $cuota->Estado = 0;
            $cuota->save();
        }
    }

    public static function exportResumen($idUsuario){

        return Excel::download(new \App\Exports\ResumenCuotas($idUsuario), 'resumen-cuotas.xlsx');
    }

}
