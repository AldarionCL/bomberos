<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocumentosCuotas extends Model
{
    protected $table = 'documentos_cuotas';
    protected $primaryKey = 'id';
    public  $timestamps = false;

    protected $fillable = [
        'idDocumento',
        'idCuota',
    ];

}
