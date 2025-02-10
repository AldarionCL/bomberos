<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Aprobadores extends Model
{

    protected $table = 'aprobadores';
    protected $primaryKey = 'id';
    protected $fillable = [
        'Orden',
        'idAprobador',
        'idSolicitudTipo',
        'Activo',
    ];

    public function aprobador(){
        return $this->belongsTo(User::class, 'idAprobador', 'id');
    }

}
