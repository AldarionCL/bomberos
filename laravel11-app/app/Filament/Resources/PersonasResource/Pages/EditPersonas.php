<?php

namespace App\Filament\Resources\PersonasResource\Pages;

use App\Filament\Resources\PersonasResource;
use App\Models\Cuota;
use App\Models\Persona;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Facades\Auth;

class EditPersonas extends EditRecord
{
    protected static string $resource = PersonasResource::class;

    public static function authorizeResourceAccess(): void
    {
        $user = Auth::user();

        if (
            $user->isRole('Administrador')
            || $user->isCargo(['Director', 'Capitán', 'Capitan', 'Teniente 1', 'Teniente 2', 'Teniente 3', 'Ayudante'])
        ) {
            return;
        }

        // Regular users can only access their own record
        abort_unless((string) $user->id === (string) request()->route('record'), 403);
    }

    protected function authorizeAccess(): void
    {
        $user = Auth::user();
        $record = $this->getRecord();

        abort_unless(
            $user->isRole('Administrador')
            || $user->isCargo(['Director', 'Capitán', 'Capitan', 'Teniente 1', 'Teniente 2', 'Teniente 3', 'Ayudante'])
            || $user->id == $record->id,
            403
        );
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make()
                ->visible(fn() => Auth::user()->isRole('Administrador'))
                ->before(function ($record) {
                    Cuota::where('idUser', $record->id)->delete();
                    Persona::where('idUsuario', $record->id)->delete();
                }),
        ];
    }
}
