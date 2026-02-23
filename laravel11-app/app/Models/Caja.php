<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Caja extends Model
{
    protected $table = 'caja';
    protected $primaryKey = 'id';

    protected $fillable = [
        'monto',
        'impuesto',
        'total',
        'id_usuario',
        'descripcion',
        'tipo',
    ];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario');
    }

    public function scopeIngreso($query)
    {
        return $query->where('tipo', 'ingreso');
    }

    public function scopeEgreso($query)
    {
        return $query->where('tipo', 'egreso');
    }


}
