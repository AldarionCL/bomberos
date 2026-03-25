<?php

namespace App\Filament\Resources\TesoreriaResource\Pages;

use App\Filament\Resources\TesoreriaResource;
use App\Models\CuotaTipo;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
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
                ->label('Generar Cuotas Anual')
                ->form([
                    DatePicker::make('fechaInicio')
                        ->default(Carbon::now()->startOfYear())
                        ->inlineLabel()
                        ->required(),
                    DatePicker::make('fechaFin')
                        ->default(Carbon::now()->lastOfYear())
                        ->inlineLabel()
                        ->required(),
                    Checkbox::make('actualizaMonto')
                    ->inlineLabel()
                    ->default(false),
                    Select::make('tipoCuota')
                        ->label('Tipo de Cuota')
                        ->options(fn()=>CuotaTipo::where('activo', 1)->pluck('nombre', 'id'))
                        ->default('Cuota Mensual'),
                ])
                ->action(function ($data) {
                    $cuotasController = new \App\Http\Controllers\CuotasController();
//                    $cuotasController->sincronizarCuotas();
                    $cuotasController->sincronizarCuotas($data['fechaInicio'], $data['fechaFin'], $data['actualizaMonto'], $data['tipoCuota']);

                    Notification::make()
                        ->success()
                        ->title('Cuotas generadas para el rango: ' . Carbon::parse($data['fechaInicio'])->format('d/m/Y') . ' a ' . Carbon::parse($data['fechaFin'])->format('d/m/Y'))
                        ->send();

                    return true;
                })
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Generar Cuotas')
                ->modalDescription('Esta seguro que desea generar las cuotas para el rango seleccionado?'),
        ];
    }

    public function getTabs(): array
    {
        return [
            'Todos' => Tab::make(),
            'Pendientes' => Tab::make()
                ->modifyQueryUsing(fn(Builder $query) => $query->whereHas('estadocuota', function ($query) {
                    $query->whereIn('Estado', ['Pendiente', 'Pendiente Aprobacion']);
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
