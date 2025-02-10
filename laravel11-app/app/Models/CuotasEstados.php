<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuotasEstados extends Model
{

    protected $table = 'cuotas_estados';
    protected $primaryKey = 'id';
    protected $fillable = [
        'Estado',
        'Descripcion',
    ];
}
