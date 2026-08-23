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

    public function cuotastipo()
    {
        return $this->belongsTo(\App\Models\CuotaTipo::class, 'TipoCuota', 'nombre');
    }

    public function scopeTipoVoluntario($query, $tipo)
    {
        return $query->where('TipoVoluntario', $tipo);
    }

    public function scopeTipoCuota($query, $tipo)
    {
        return $query->where('TipoCuota', $tipo);
    }

    /** Monto vigente para un tipo de cuota, priorizando el precio específico del tipo de voluntario. */
    public static function vigentePara(string $nombreTipo, string $tipoVoluntario = 'miembro'): int
    {
        $precio = self::where('TipoCuota', $nombreTipo)
            ->where('TipoVoluntario', $tipoVoluntario)
            ->orderByDesc('id')
            ->first()
            ?? self::where('TipoCuota', $nombreTipo)->orderByDesc('id')->first();

        return (int) ($precio?->Monto ?? 0);
    }
}
