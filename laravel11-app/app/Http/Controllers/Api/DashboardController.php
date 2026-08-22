<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\Cuota;
use App\Models\Gastos;
use App\Models\Persona;
use App\Models\Solicitud;
use App\Services\CuotaMensualService;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private CuotaMensualService $cuotaMensualService)
    {
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $esTesoreria = $user->isRole('Administrador') || $user->isCargo('Tesorero');

        $data = [
            'personal' => $this->widgetsPersonales($user),
        ];

        if ($esTesoreria) {
            $data['organizacion'] = $this->widgetsOrganizacion();
        }

        return response()->json($data);
    }

    private function widgetsPersonales($user): array
    {
        $persona = $user->persona;

        $estadoSocio = $persona?->estado?->Estado ?? 'Desconocido';

        $licencia = Solicitud::where('AsociadoA', $user->id)
            ->whereIn('TipoSolicitud', [3, 4])
            ->where('Estado', 1)
            ->where('FechaDesde', '<=', Carbon::now())
            ->where('FechaHasta', '>=', Carbon::now())
            ->first();

        $diasInactividad = $licencia ? (int) Carbon::now()->diffInDays($licencia->FechaHasta) : 0;

        $cuotaVigente = Cuota::where('idUser', $user->id)
            ->where('FechaPeriodo', '<=', Carbon::now()->format('Y-m-d'))
            ->where('FechaVencimiento', '>=', Carbon::now()->format('Y-m-d'))
            ->first();

        $pendientes = $persona ? $this->cuotaMensualService->periodosPendientes($persona) : collect();
        $cuotasPendientesReales = Cuota::where('idUser', $user->id)->whereIn('Estado', [1, 5])->count();

        return [
            'estadoSocio' => $estadoSocio,
            'diasInactividad' => $diasInactividad,
            'estadoCuotaVigente' => $cuotaVigente?->estadocuota?->Estado ?? 'Sin registro',
            'periodoCuotaVigente' => optional($cuotaVigente?->FechaPeriodo)->format('Y-m-d'),
            'cuotasPendientes' => $cuotasPendientesReales + $pendientes->count(),
            'montoCuotaMensual' => $this->cuotaMensualService->montoVigente($persona?->TipoVoluntario),
        ];
    }

    private function widgetsOrganizacion(): array
    {
        $totalCaja = (float) Caja::sum('total');
        $ingresosCuotas = (float) Caja::where('tipo', 'Ingreso cuota')->sum('total');
        $egresos = abs((float) Caja::where('tipo', 'Egreso')->sum('total'));
        $totalGastos = abs((float) Gastos::sum('MontoTotal'));

        $seriesMeses = collect(range(11, 0))->map(function ($i) {
            $mes = Carbon::now()->subMonths($i);
            $ingresos = Caja::where('tipo', 'Ingreso cuota')
                ->whereYear('created_at', $mes->year)
                ->whereMonth('created_at', $mes->month)
                ->sum('total');
            $gastosMes = abs(Gastos::whereYear('FechaGasto', $mes->year)
                ->whereMonth('FechaGasto', $mes->month)
                ->sum('MontoTotal'));

            return [
                'mes' => $mes->format('Y-m'),
                'ingresos' => (float) $ingresos,
                'egresos' => (float) $gastosMes,
            ];
        })->values();

        $cuotasPorEstado = Cuota::query()
            ->selectRaw('Estado, count(*) as total')
            ->groupBy('Estado')
            ->pluck('total', 'Estado');

        $estadosLabel = [1 => 'Pendiente', 2 => 'Aprobado', 3 => 'Rechazado', 4 => 'Cancelado', 5 => 'Pendiente Aprobación'];

        return [
            'totalCaja' => $totalCaja,
            'ingresosCuotas' => $ingresosCuotas,
            'egresos' => $egresos,
            'totalGastos' => $totalGastos,
            'sociosActivos' => Persona::where('Activo', 1)->count(),
            'cuotasPorAprobar' => Cuota::where('Estado', 5)->count(),
            'montoPorAprobar' => (float) Cuota::where('Estado', 5)->sum('Pendiente'),
            'serieMensual' => $seriesMeses,
            'cuotasPorEstado' => collect($estadosLabel)->map(fn ($label, $id) => [
                'estado' => $label,
                'total' => (int) ($cuotasPorEstado[$id] ?? 0),
            ])->values(),
        ];
    }
}
