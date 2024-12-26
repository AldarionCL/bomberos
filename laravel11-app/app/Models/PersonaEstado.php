<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonaEstado extends Model
{

    protected $table = 'persona_estados';
    protected $fillable = [
        'estado',
        'descripcion',
    ];
}
