<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PersonaCargo extends Model
{
    protected $table = 'persona_cargos';
    protected $fillable = [
        'cargo',
        'descripcion',
    ];
}
