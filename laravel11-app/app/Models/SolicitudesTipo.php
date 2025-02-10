<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SolicitudesTipo extends Model
{
    protected $table = 'solicitudes_tipos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'Tipo',
        'Descripcion',
        'Locked',
    ];

    public function aprobadores(){
        return $this->hasMany(Aprobadores::class,'idSolicitudTipo');
    }

}
