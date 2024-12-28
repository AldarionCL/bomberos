<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{

    protected $table = 'cuotas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'idUser',
        'FechaPeriodo',
        'FechaVencimiento',
        'FechaPago',
        'Estado',
        'Documento',
        'DocumentoArchivo',
        'Monto',
        'Pendiente',
        'Recaudado',
    ];

    protected $dates = [
        'FechaPeriodo',
        'FechaVencimiento',
        'FechaPago',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'idUser');
    }

    public function estadocuota(){
        return $this->hasOne(CuotasEstados::class, 'id', 'Estado');
    }
}
