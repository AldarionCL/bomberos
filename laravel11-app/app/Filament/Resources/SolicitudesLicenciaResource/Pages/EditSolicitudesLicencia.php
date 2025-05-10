<?php

namespace App\Filament\Resources\SolicitudesLicenciaResource\Pages;

use App\Filament\Resources\SolicitudesLicenciaResource;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditSolicitudesLicencia extends EditRecord
{
    protected static string $resource = SolicitudesLicenciaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function beforeSave(){
        $record = $this->getRecord();
        if ($record->NoGuarda) {
            dump($record->NoGuarda);
            return false;
        } else {
            return true;
        }
    }
}
