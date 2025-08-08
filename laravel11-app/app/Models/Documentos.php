<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Documentos extends Model
{
    //
    protected $table = 'documentos';
    protected $primaryKey = 'id';
    protected $fillable = [
        'Nombre',
        'Descripcion',
        'TipoArchivo',
        'TipoDocumento',
        'Path',
        'AsociadoA',
        'idSolicitud'
    ];

    public function tipo()
    {
        return $this->hasOne(DocumentosTipo::class, 'id', 'TipoDocumento');
    }

    public function asociado()
    {
        return $this->hasOne(User::class, 'id', 'AsociadoA');
    }

    public function solicitud()
    {
        return $this->belongsTo(Solicitud::class, 'idSolicitud', 'id');
    }

    public function cuotas()
    {
        return $this->belongsToMany(
            Cuota::class,
            'documentos_cuotas',
            'idDocumento',
            'idCuota'
        );
    }
}
