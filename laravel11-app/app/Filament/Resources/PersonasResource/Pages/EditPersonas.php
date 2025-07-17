<?php

namespace App\Filament\Resources\PersonasResource\Pages;

use App\Filament\Resources\PersonasResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPersonas extends EditRecord
{
    protected static string $resource = PersonasResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
            ->before(function ($record) {
                if($record->cuotas) {
                    if($record->cuotas->count() > 0) {
                        // If there are associated cuotas, prevent deletion
                        return Actions\Action::make('error')
                            ->label('No se puede eliminar, existen cuotas asociadas')
                            ->color('danger');
                    }

                }
            }),
        ];
    }
}
