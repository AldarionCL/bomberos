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
        'idDocumento',
        'Estado',
        'FechaPublicacion',
        'FechaExpiracion',
        'createdBy'
    ];

    protected function casts(): array
    {
        return [
            'FechaPublicacion' => 'date:Y-m-d',
            'FechaExpiracion' => 'date:Y-m-d',
        ];
    }

    public function user(){
        return $this->hasOne(User::class, 'id', 'createdBy');
    }

    public function documento(){
        return $this->belongsTo(Documentos::class, 'idDocumento');
    }
}
