<?php

namespace App\Exports;

use App\Models\Cuota;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class ResumenCuotas implements FromView, ShouldAutoSize
{
    use Exportable;

    private $idUsuario;

    public function __construct($idUsuario)
    {
        $this->idUsuario = $idUsuario;
    }

    public function view(): View
    {
        $fechaInicio = now()->startOfYear()->subMonth();
        $fechaFin = now()->endOfYear();

        $cuotas = Cuota::where('idUser', $this->idUsuario)
            ->whereBetween('FechaPeriodo', [$fechaInicio, $fechaFin])
//            ->where('Estado', 2) // Estado 2: Pagada
//            ->where('AprobadoPor', '<>', '') // Recaudado menor o igual al monto
            ->orderBy('FechaPeriodo', 'asc')
            ->get();

        $cargos = [];
        $abonos = [];
        $totalAbonos = 0;
        $totalCargos = 0;

        foreach ($cuotas as $cuota) {
            $mes = Carbon::parse($cuota->FechaPeriodo)->format('Y-m');
            $fechaPago = Carbon::parse($cuota->FechaPago)->format('Y-m-d');

            if (!isset($cargos[$mes])) $cargos[$mes] = [];
            if (!isset($cargos[$mes][$cuota->TipoCuota])) $cargos[$mes][$cuota->TipoCuota] = 0;

            $recaudado = ($cuota->Recaudado > $cuota->Monto) ? $cuota->Monto : $cuota->Recaudado;
            $saldo = $cuota->Recaudado - $cuota->Monto;

            $cargos[$mes][$cuota->TipoCuota] += $recaudado;
            $totalCargos += $recaudado;

            if ($saldo > 0) {
                $abonos[$fechaPago] = $saldo;
                $totalAbonos += $saldo;
            }
        }

        $usuario = User::find($this->idUsuario);

//        return view('filament.pages.resumen-cuotas', ['cargos' => $this->cargos, 'usuario' => $this->usuario]);
        return view('filament.pages.resumen-cuotas', [
            'cargos' => $cargos,
            'abonos' => $abonos,
            'usuario' => $usuario,
            'totalCargos' => $totalCargos,
            'totalAbonos' => $totalAbonos,
        ]);
    }
}
