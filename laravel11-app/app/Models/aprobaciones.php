<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class aprobaciones extends Model
{
    protected $table = 'aprobaciones';
    protected $primaryKey = 'id';
    protected $fillable = [
        'idSolicitud',
        'idAprobador',
        'Orden',
        'Estado',
        'FechaAprobacion',
    ];
}
