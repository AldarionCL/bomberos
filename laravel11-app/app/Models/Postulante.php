<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Postulante extends Model
{
    protected $table = 'postulantes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'idSolicitud',
        'idCargo',
        'RutPostulante',
        'NombrePostulante',
        'TelefonoPostulante',
        'CorreoPostulante',
        'DireccionPostulante',
        'ComunaPostulante',
        'Observaciones',
        'NivelEstudioPostulante',
        'FechaNacimientoPostulante',
        'EdadPostulante',
        'SexoPostulante',
        'EstadoCivilPostulante',
        'OcupacionPostulante',
        'FotoPostulante',
        'NacionalidadPostulante',
        'SituacionMilitarPostulante',
        'LugarOcupacionPostulante',
        'GrupoSanguineoPostulante',
        'TallaZapatosPostulante',
        'TallaPantalonPostulante',
        'TallaCamisaPostulante',
        'TallaChaquetaPostulante',
        'TallaSombreroPostulante',
    ];

    protected $dates = [
        'FechaNacimientoPostulante',
    ];

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'id', 'idSolicitud');
    }
}
