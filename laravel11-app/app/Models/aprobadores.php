<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class aprobadores extends Model
{

    protected $table = 'aprobadores';
    protected $primaryKey = 'id';
    protected $fillable = [
        'Orden',
        'idAprobador',
        'Activo',
    ];

}
