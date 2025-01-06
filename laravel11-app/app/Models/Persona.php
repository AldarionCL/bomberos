<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class Persona extends Model
{
    protected $table = 'personas';
    protected $primaryKey = 'id';

    protected $fillable = [
        'idUsuario',
        'idRole',
        'idCargo',
        'idEstado',
        'Rut',
        'Telefono',
        'Direccion',
        'FechaNacimiento',
        'FechaReclutamiento',
        'Sexo',
        'EstadoCivil',
        'Ocupacion',
        'Foto',
        'Edad',
        'Nacionalidad',
        'SituacionMilitar',
        'Activo',
    ];

    protected $dates = [
        'FechaNacimiento',
        'FechaReclutamiento'
    ];

    public function user()
    {
        return $this->hasOne(User::class, 'id', 'idUsuario');
    }

    public function role()
    {
        return $this->hasOne(UserRole::class, 'id', 'idRole');
    }

    public function cargo()
    {
        return $this->hasOne(PersonaCargo::class, 'id', 'idCargo');
    }

    public function estado()
    {
        return $this->hasOne(PersonaEstado::class, 'id', 'idEstado');
    }

    public function cuotas()
    {
        return $this->hasMany(Cuota::class, 'idUser', 'idUsuario');
    }


    public function scopeIsRole($query, $roles = [])
    {
        $query->with(['role' => function ($query) use ($roles) {
            if (is_array($roles)) {
                foreach ($roles as $k => $r) {
                    if ($query->role->rol == $r) {
                        return true;
                    }
                }
                return false;
            } else {
                return $query->role->rol == $roles;
            }
        }]);

    }

}
