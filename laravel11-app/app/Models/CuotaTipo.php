<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CuotaTipo extends Model
{
    use HasFactory;

    protected $table = 'cuotas_tipo';

    protected $fillable = [
        'nombre',
        'descripcion',
        'tipoCobro',
        'activo',
        'es_mensual',
        'es_cuota_inscripcion',
    ];

    public function cuotas()
    {
        return $this->hasMany(Cuota::class, 'idCuotaTipo');
    }
}
