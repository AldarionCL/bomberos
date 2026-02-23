<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Gastos extends Model
{
    protected $table = 'gastos';
    protected $primaryKey = 'id';

    protected $fillable = [
        'TipoGasto',
        'MontoGasto',
        'MontoIva',
        'MontoTotal',
        'Descripcion',
        'FechaGasto',
        'AsociadoA',
        'idCaja'
    ];

    public function caja(){
        return $this->belongsTo(Caja::class, 'idCaja');
    }

    public function documento()
    {
        return $this->hasOne(DocumentosGastos::class, 'idGasto');
    }
}
