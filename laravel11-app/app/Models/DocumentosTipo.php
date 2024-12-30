<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentosTipo extends Model
{
    protected $table = 'documentos_tipos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'Tipo',
        'Descripcion',
    ];

}
