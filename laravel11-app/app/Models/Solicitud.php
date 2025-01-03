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
        'AsociadoA',
        'RutPostulante',
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

    public function solicitante(){
        return $this->hasOne(User::class, 'id', 'SolicitadoPor');
    }

    public function documentos(){
        return $this->hasMany(Documentos::class, 'idSolicitud', 'id');
    }

    public function aprobaciones(){
        return $this->hasMany(Aprobaciones::class, 'idSolicitud', 'id');
    }

    public function asociado(){
        return $this->hasOne(User::class, 'id', 'AsociadoA');
    }
}
