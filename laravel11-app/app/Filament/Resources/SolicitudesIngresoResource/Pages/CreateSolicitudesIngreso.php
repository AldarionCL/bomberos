<?php

namespace App\Filament\Resources\SolicitudesIngresoResource\Pages;

use App\Filament\Resources\SolicitudesIngresoResource;
use App\Models\Aprobaciones;
use App\Models\Aprobadores;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateSolicitudesIngreso extends CreateRecord
{
    protected static string $resource = SolicitudesIngresoResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data["TipoSolicitud"] = 2;
        $data["Fecha_registro"] = Carbon::today()->format('Y-m-d');
        $data["SolicitadoPor"] = Auth::user()->id;
        $data["Estado"] = 0;
        $data["AsociadoA"] = 0;

        return $data;
    }

    protected function afterCreate(){
        $record = $this->getRecord();
        $aprobadores = Aprobadores::where('idSolicitudTipo', 2)->get();
        foreach ($aprobadores as $aprobador){
            Aprobaciones::create([
                'idSolicitud' => $record->id ,
                'idAprobador' => $aprobador->idAprobador,
                'Orden' => $aprobador->Orden,
                'Estado' => 0,
                'FechaAprobacion' => null,
            ]);
        }
    }
}
