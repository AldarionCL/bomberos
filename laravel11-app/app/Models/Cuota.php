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
        'TipoCuota',
        'AprobadoPor',
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

    public function aprobador(){
        return $this->hasOne(User::class, 'id', 'AprobadoPor');
    }

    public function scopeThisMonth($query,$mes=null)
    {
        return $query->whereMonth('FechaPeriodo', now()->month)
                     ->whereYear('FechaPeriodo', now()->year);
    }
}
