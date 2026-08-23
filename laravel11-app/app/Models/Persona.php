<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

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
        'Nombre',
        'Telefono',
        'TelefonoEmergencia',
        'Correo',
        'Direccion',
        'Comuna',
        'Observaciones',
        'NivelEstudio',
        'FechaNacimiento',
        'FechaReclutamiento',
        'Edad',
        'Sexo',
        'EstadoCivil',
        'Ocupacion',
        'Foto',
        'Nacionalidad',
        'SituacionMilitar',
        'LugarOcupacion',
        'GrupoSanguineo',
        'TallaZapatos',
        'TallaPantalon',
        'TallaCamisa',
        'TallaChaqueta',
        'TallaSombrero',
        'TipoVoluntario',
        'Activo',
    ];

    protected function casts(): array
    {
        return [
            'FechaNacimiento' => 'date:Y-m-d',
            'FechaReclutamiento' => 'date:Y-m-d',
        ];
    }

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
    public function cuotasMes()
    {
        return $this->hasMany(Cuota::class, 'idUser', 'idUsuario')
            ->whereMonth('FechaPeriodo', now()->month)
            ->whereYear('FechaPeriodo', now()->year);
    }

    public function documentos(){
        return $this->hasMany(Documentos::class, 'AsociadoA', 'idUsuario');
    }

    public function solicitudes(){
        return $this->hasMany(Solicitud::class, 'AsociadoA', 'idUsuario');
    }

    /** Token estable usado para el carnet de socio verificable (QR). */
    public function tokenCarnet(): string
    {
        if (! $this->token) {
            $this->token = Str::random(40);
            $this->save();
        }

        return $this->token;
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
