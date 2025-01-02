<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Solicitud extends Model
{
    protected $table = 'solicitudes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'TipoSolicitud',
        'Estado',
        'Fecha_registro',
        'SolicitadoPor',
        'NombrePostulante',
        'TelefonoPostulante',
        'CorreoPostulante',
        'DireccionPostulante',
        'Observaciones',
        'NivelEstudioPostulante',
        'FechaNacimientoPostulante',
        'SexoPostulante',
        'EstadoCivilPostulante',
        'OcupacionPostulante',
    ];
}
