<?php

namespace App\Observers;

use App\Models\Gastos;
use App\Models\Caja;
use Illuminate\Support\Facades\Auth;

class GastosObserver
{
    public function creating(Gastos $gasto): void
    {
        $gasto->MontoGasto = -abs($gasto->MontoGasto);
        $gasto->MontoIva = -abs($gasto->MontoIva);
        $gasto->MontoTotal = -abs($gasto->MontoTotal);
    }

    /**
     * Handle the Gastos "created" event.
     */
    public function created(Gastos $gasto): void
    {
        Caja::create([
            'monto' => $gasto->MontoGasto,
            'impuesto' => $gasto->MontoIva,
            'total' => $gasto->MontoTotal,
            'id_usuario' => Auth::id(),
            'descripcion' => "Gasto: {$gasto->TipoGasto} - {$gasto->Descripcion}",
            'tipo' => 'Egreso',
        ]);
    }
}
