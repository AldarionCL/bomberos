<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Noticias extends Model
{
    protected $table = 'noticias';
    protected $primaryKey = 'id';
    protected $fillable = [
        'Titulo',
        'Subtitulo',
        'Contenido',
        'Imagen',
        'Estado',
        'FechaPublicacion',
        'FechaExpiracion',
        'createdBy'
    ];

    public function user(){
        return $this->hasOne(User::class, 'id', 'createdBy');
    }
}
