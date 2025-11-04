<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PrecioCuotas extends Model
{
    protected $table = 'precio_cuotas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'TipoVoluntario',
        'TipoCuota',
        'Monto',
        'periodo',
    ];

    protected $casts = [
        'periodo' => 'date',
    ];


    public function scopeTipoVoluntario($query, $tipo)
    {
        return $query->where('TipoVoluntario', $tipo);
    }

    public function scopeTipoCuota($query, $tipo)
    {
        return $query->where('TipoCuota', $tipo);
    }
}
