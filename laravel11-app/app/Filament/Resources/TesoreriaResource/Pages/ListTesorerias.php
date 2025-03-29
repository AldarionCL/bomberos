<?php

namespace App\Filament\Resources\TesoreriaResource\Pages;

use App\Filament\Resources\TesoreriaResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListTesorerias extends ListRecords
{
    protected static string $resource = TesoreriaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('generarCuotas')
                ->action(function () {
                    $cuotasController = new \App\Http\Controllers\CuotasController();
                    $cuotasController->sincronizarCuotas();
                    return true;
                })
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Generar Cuotas')
                ->modalDescription('Esta seguro que desea generar las cuotas del año completo?'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Todos' => Tab::make(),
            'Pendientes' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('estadocuota', function ($query) {
                    $query->where('Estado', 'Pendiente');
                })),

            'Aprobados' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('estadocuota', function ($query) {
                    $query->where('Estado', 'Aprobado');
                })),
            'Cancelados' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('estadocuota', function ($query) {
                    $query->where('Estado', 'Cancelado');
                })),
            'Rechazados' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('estadocuota', function ($query) {
                    $query->where('Estado', 'Rechazado');
                })),
        ];
    }
}
