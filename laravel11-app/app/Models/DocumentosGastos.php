<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentosGastos extends Model
{
    protected $fillable = [
        'idGasto',
        'nombre',
        'ruta_archivo',
    ];

    public function gasto()
    {
        return $this->belongsTo(Gastos::class, 'idGasto');
    }
}
