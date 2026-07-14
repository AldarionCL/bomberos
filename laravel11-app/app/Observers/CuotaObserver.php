<?php

namespace App\Observers;

use App\Models\Cuota;
use App\Models\Caja;
use App\Models\CuotasEstados;
use Illuminate\Support\Facades\Auth;

class CuotaObserver
{
    /**
     * Handle the Cuota "created" event.
     */
    public function created(Cuota $cuota): void
    {
        $this->registrarCaja($cuota);
    }

    /**
     * Handle the Cuota "updated" event.
     */
    public function updated(Cuota $cuota): void
    {
        // Si el estado ha cambiado
        if ($cuota->wasChanged('Estado')) {
            $this->registrarCaja($cuota);
        }
    }

    /**
     * Handle the Cuota "deleted" event.
     */
    public function deleted(Cuota $cuota): void
    {
        $descripcion = "Pago de cuota - Usuario ID: {$cuota->idUser} - Periodo: {$cuota->FechaPeriodo}";
        Caja::where('descripcion', $descripcion)->delete();
    }

    /**
     * Registra el ingreso en caja si el estado es Aprobado o Pagada.
     */
    protected function registrarCaja(Cuota $cuota): void
    {
        $nuevoEstado = CuotasEstados::find($cuota->Estado);

        if ($nuevoEstado && ($nuevoEstado->Estado === 'Pagada' || $nuevoEstado->Estado === 'Aprobado')) {
            Caja::create([
                'monto' => $cuota->Monto,
                'impuesto' => 0,
                'total' => $cuota->Monto,
                'id_usuario' => Auth::id(),
                'descripcion' => "Pago de cuota - Usuario ID: {$cuota->idUser} - Periodo: {$cuota->FechaPeriodo}",
                'tipo' => 'Ingreso cuota',
            ]);
        }
    }
}
