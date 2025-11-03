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

    public function sincronizarCuotas($fechaInicio, $fechaFin, $actualizaMontos = false)
    {

        // Trae las personas activas
        $personas = Persona::where('Activo', 1)
            ->orderBy('FechaReclutamiento', 'asc')
            ->get();

        foreach ($personas as $persona) {
            $fechaHoy = Carbon::now();
            $fechaNacimiento = Carbon::parse($persona->FechaNacimiento);
            $fechaReclutamiento = Carbon::parse($persona->FechaReclutamiento);
            $edad = $fechaHoy->diffInYears($fechaNacimiento) * -1;
            $tipoVoluntario = $persona->TipoVoluntario ?? 'voluntario';
            $antiguedad = $fechaReclutamiento->diffInYears(Carbon::now()) * -1;

            $exento = false;
            if ($antiguedad >= 50) $exento = true;

            if (!$exento) {
                // Trae ultima cuota creada
                $tiposCuota = PrecioCuotas::where('TipoVoluntario', $tipoVoluntario)
                    ->get();

                $fechaInicioProceso = Carbon::parse($fechaInicio)->firstOfMonth();
                $fechaFinProceso = Carbon::parse($fechaFin)->lastOfMonth();
                $diffMeses = round($fechaFinProceso->diffInMonths($fechaInicioProceso)) * -1;

                for ($i = 0; $i <= $diffMeses; $i++) {

                    // calcula fecha periodo y vencimiento, agregando
                    $fechaPeriodo = $fechaInicioProceso->copy()->addMonth();
                    $fechaVencimiento = $fechaPeriodo->copy()->lastOfMonth();

                    // por cada tipo de cuota
                    foreach ($tiposCuota as $tipo) {
                        // si el tipo de cuota tiene monto asignado
                        if ($tipo->Monto > 0) {
                            $monto = $tipo->Monto;
                            $tipoCuota = $tipo->TipoCuota;

                            $existeCuota = Cuota::where('idUser', $persona->idUsuario)
                                ->where('FechaPeriodo', $fechaPeriodo->format('Y-m-01'))
                                ->where('TipoCuota', $tipoCuota)
                                ->exists();

                            if (!$existeCuota) {
                                $cuota = Cuota::Create(
                                    [
                                        'idUser' => $persona->idUsuario,
                                        'FechaPeriodo' => $fechaPeriodo->format('Y-m-01'),
                                        'FechaVencimiento' => $fechaVencimiento->format('Y-m-d'),
                                        'Estado' => 1,
                                        'Monto' => $monto,
                                        'TipoCuota' => $tipoCuota,
                                        'Pendiente' => $monto,
                                        'Recaudado' => 0,
                                    ]);
                            } else {
                                if ($actualizaMontos) {
                                    // Actualiza monto en caso de que haya cambiado
                                    Cuota::where('idUser', $persona->idUsuario)
                                        ->where('FechaPeriodo', $fechaPeriodo->format('Y-m-01'))
                                        ->where('TipoCuota', $tipoCuota)
                                        ->where('Estado', 1)
                                        ->update(
                                            [
                                                'Monto' => $monto,
                                                'FechaVencimiento' => $fechaPeriodo->lastOfMonth()->format('Y-m-d'),
                                            ]);
                                }
                            }
                        }
                    }
                }
            }
        }
    }


    public
    function sincronizarUserPersona()
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

    public
    function revisaCuotasVencidas()
    {
        $cuotas = Cuota::where('Estado', 1)
            ->where('FechaVencimiento', '<', Carbon::now())
            ->get();

        foreach ($cuotas as $cuota) {
            $cuota->Estado = 0;
            $cuota->save();
        }
    }

    public
    static function exportResumen($idUsuario)
    {

        return Excel::download(new \App\Exports\ResumenCuotas($idUsuario), 'resumen-cuotas.xlsx');
    }

}
