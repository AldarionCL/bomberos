<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Aprobaciones extends Model
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

    public function aprobador()
    {
        return $this->hasOne(User::class, 'id', 'idAprobador');
    }


}
