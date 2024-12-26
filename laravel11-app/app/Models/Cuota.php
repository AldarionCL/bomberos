<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cuota extends Model
{

    protected $table = 'cuotas';
    protected $primaryKey = 'id';
    protected $fillable = [
        'idUser',
        'fechaPeriodo',
        'fechaVencimiento',
        'fechaPago',
        'estado',
        'documento',
        'documentoArchivo',
    ];

    public function user(){
        return $this->belongsTo(User::class, 'idUser');
    }
}
