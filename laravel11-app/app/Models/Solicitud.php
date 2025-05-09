<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class Solicitud extends Model
{
    protected $table = 'solicitudes';
    protected $primaryKey = 'id';

    protected $fillable = [
        'TipoSolicitud',
        'Estado',
        'Fecha_registro',
        'SolicitadoPor',
        'AsociadoA',
        'Observaciones',
        'FechaDesde',
        'FechaHasta',
        'DiasHabiles'
    ];

    public function solicitante()
    {
        return $this->hasOne(User::class, 'id', 'SolicitadoPor');
    }

    public function documentos()
    {
        return $this->hasMany(Documentos::class, 'idSolicitud', 'id');
    }

    public function aprobaciones()
    {
        return $this->hasMany(Aprobaciones::class, 'idSolicitud', 'id');
    }

    public function asociado()
    {
        return $this->hasOne(User::class, 'id', 'AsociadoA');
    }

    public function persona()
    {
        return $this->hasOne(Persona::class, 'id', 'AsociadoA');
    }

    public function postulante()
    {
        return $this->hasOne(Postulante::class, 'idSolicitud', 'id');
    }

    public function tipo()
    {
        return $this->hasOne(SolicitudesTipo::class, 'id', 'TipoSolicitud');
    }

    public function calculaDiasHabiles($desde, $hasta)
    {
        $desde = \Carbon\Carbon::parse($desde);
        $hasta = \Carbon\Carbon::parse($hasta);

        $diasHabiles = 0;

        while ($desde <= $hasta) {
            if ($desde->isWeekday()) {
                $diasHabiles++;
            }
            $desde->addDay();
        }

        return $diasHabiles;
    }

    public function scopeVerificaDiasDisponibles($query, $usuario, $dias, $tipo = 3)
    {
        Log::info('Verificando dias de usuario: ' . $usuario);
        $diasTotales = $query->where('AsociadoA', $usuario)
            ->where('Estado', 1)
            ->where('TipoSolicitud', $tipo)
            ->where('FechaDesde', '=>', Carbon::now()->firstOfYear()->format('Y-m-d'))
            ->where('FechaHasta', now())
            ->sum('DiasHabiles');
        Log::info('Dias totales: ' . $diasTotales);

        if ($tipo == 3) {
            $calculo = $diasTotales + $dias;
            Log::info('Dias totales + nuevos: ' . $calculo);
            if ($calculo > 30) {
                return false;
            }
        } elseif ($tipo == 4) {
            $calculo = $diasTotales + $dias;
            Log::info('Dias totales + nuevos: ' . $calculo);
            if ($calculo > 180) {
                return false;
            }
        }
        return true;
    }

}
