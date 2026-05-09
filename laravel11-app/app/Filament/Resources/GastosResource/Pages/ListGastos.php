<?php

namespace App\Filament\Resources\GastosResource\Pages;

use App\Filament\Resources\GastosResource;
use App\Models\Gastos;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Forms\Components\FileUpload;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListGastos extends ListRecords
{
    protected static string $resource = GastosResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('import')
                ->label('Importar Egresos')
                ->color('info')
                ->icon('heroicon-o-arrow-up-tray')
                ->form([
                    FileUpload::make('csv_file')
                        ->label('Archivo CSV (Fecha, Descripcion, Monto)')
                        ->disk('public')
                        ->directory('temp-imports')
                        ->required()
                        ->acceptedFileTypes(['text/csv', 'application/vnd.ms-excel', 'text/plain']),
                ])
                ->action(function (array $data): void {
                    $filePath = Storage::disk('public')->path($data['csv_file']);

                    if (($handle = fopen($filePath, "r")) !== FALSE) {
                        $count = 0;
                        while (($row = fgetcsv($handle, 1000, ";")) !== FALSE) {
                            // Si no tiene al menos 3 columnas con el delimitador principal, intentar con coma
                            if (count($row) < 3) {
                                rewind($handle);
                                $count = 0;
                                while (($row = fgetcsv($handle, 1000, ",")) !== FALSE) {
                                    $this->processRow($row, $count);
                                    $count++;
                                }
                                break;
                            }

                            $this->processRow($row, $count);
                            $count++;
                        }
                        fclose($handle);
                        Storage::disk('public')->delete($data['csv_file']);

                        Notification::make()
                            ->title('Importación completada')
                            ->success()
                            ->send();
                    }
                }),
            Actions\CreateAction::make()
                ->label('Registrar Egreso'),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            GastosResource::getHeaderWidgets()[0],
        ];
    }

    private function processRow(array $row, int $count): void
    {
        // Ignorar encabezado si existe o filas vacías
        if ($count === 0 && (!isset($row[2]) || !is_numeric(str_replace(['$', '.', ','], '', $row[2])))) {
            return;
        }

        if (count($row) >= 3) {
            $fecha = Carbon::parse(trim($row[0]))->format('Y-m-d');
            $descripcion = trim($row[1]);
            $montoStr = str_replace(['$', '.'], '', $row[2]);
            $montoStr = str_replace(',', '.', $montoStr);
            $monto = abs((float) $montoStr);

            if ($monto > 0) {
                $total = $monto;
                $neto = $total / 1.19;
                $iva = $total - $neto;

                Gastos::create([
                    'FechaGasto' => $fecha,
                    'Descripcion' => $descripcion,
                    'MontoGasto' => $neto,
                    'MontoIva' => $iva,
                    'MontoTotal' => $total,
                    'TipoGasto' => 'Importado', // Valor por defecto
                ]);
            }
        }
    }
}
