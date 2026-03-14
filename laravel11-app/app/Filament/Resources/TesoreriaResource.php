<?php

namespace App\Filament\Resources;

use App\Filament\Exports\CuotasExporter;
use App\Filament\Resources\TesoreriaResource\Pages;
use App\Filament\Resources\TesoreriaResource\RelationManagers;
use App\Models\Cuota;
use App\Models\CuotasEstados;
use App\Models\User;
use App\Models\PrecioCuotas;
use Carbon\Carbon;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Faker\Provider\Text;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TesoreriaResource extends Resource
{
    protected static ?string $model = Cuota::class;

    protected static ?string $navigationIcon = 'heroicon-s-rectangle-stack';
    protected static ?string $navigationGroup = 'Tesoreria';
    protected static ?int $navigationSort = 5;
    protected static ?string $navigationLabel = 'Admin. Cuotas';
    protected static ?string $label = 'Cuota';
    protected static ?string $pluralLabel = 'Cuotas';

    public static function canAccess(): bool
    {
        return Auth::user()->isRole('Administrador') || Auth::user()->isCargo('Tesorero');
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                    ->schema([
                        Select::make('idUser')
                            ->relationship('user', 'name')
                            ->label('Persona')
                            ->reactive()
                            ->required(),

                        Select::make('TipoCuota')
                            ->options(function ($record, $get,$set) {
                                $usuario = $get('idUser');
                                if ($usuario) {
                                    $user = \App\Models\User::find($usuario);
                                    $tipoVoluntario = $user->persona->TipoVoluntario ?? null;
                                    $fechaNacimiento = $user->persona->FechaNacimiento ?? null;
                                    $fechaNacimiento = Carbon::parse($fechaNacimiento);
                                    $edad = Carbon::now()->diffInYears($fechaNacimiento) * -1;
                                    $antiguedad = Carbon::now()->diffInYears($user->persona->FechaReclutamiento) * -1;

                                    if ($antiguedad < 50) {
                                        $tiposCuotas = PrecioCuotas::where('TipoVoluntario', $tipoVoluntario)
                                            ->where('Monto', '>', 0)
                                            ->get();
                                        foreach ($tiposCuotas as $tipoCuota) {
                                            $options[$tipoCuota->TipoCuota] = ucwords(str_replace('_', ' ', strtolower($tipoCuota->TipoCuota)));;
                                        }
                                    } else {
                                        Notification::make()
                                            ->title('Atención')
                                            ->body('El usuario tiene una antiguedad de 50 años o mas, esta exedente de cuota mensual.')
                                            ->warning()
                                            ->send();
                                        $set('Monto', 0);

                                        $options = [];
                                    }
                                }
                                $options['Otros'] = 'Otros';
                                return $options ?? [];

                            })
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($state, $set, $get, $record) {
                                $tipoCuota = $state;
                                $usuario = $get('idUser');
                                if ($usuario) {
                                    $user = \App\Models\User::find($usuario);
                                    $tipoVoluntario = $user->persona->TipoVoluntario ?? null;
                                    if ($tipoCuota) {
                                        $cuotaMonto = \App\Models\PrecioCuotas::where('TipoCuota', $tipoCuota)
                                            ->where('TipoVoluntario', $tipoVoluntario)
                                            ->first();

                                        if ($cuotaMonto) {
                                            $set('Monto', $cuotaMonto->Monto);
                                            $set('Pendiente', $cuotaMonto->Monto);
                                            $set('Recaudado', 0);
                                        } else {
                                            Notification::make()
                                                ->title('Atención')
                                                ->body('No se encontró el monto para la cuota seleccionada. Ingrésela manualmente')
                                                ->danger()
                                                ->send();
                                        }
                                    }
                                }


                            })
                            ->required(),

                        Forms\Components\TextInput::make('Monto')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\TextInput::make('Pendiente')
                            ->numeric()
                            ->prefix('$')
                            ->required(),
                        Forms\Components\TextInput::make('Recaudado')
                            ->numeric()
                            ->prefix('$')
                            ->required(),


                        Select::make('Estado')
                            ->options(fn() => \App\Models\CuotasEstados::all()->pluck('Estado', 'id'))
                            ->default(1)
                            ->label('Estado'),

//                    DatePicker::make('fechaPeriodo')->label('Fecha de Periodo'),
                        Flatpickr::make('FechaPeriodo')
                            ->label('Periodo Desde')
                            ->required(),

                        Flatpickr::make('FechaVencimiento')
                            ->label('Fecha de Vencimiento')
                            ->required(),


                        Flatpickr::make('FechaPago')->label('Fecha de Pago')
                            ->default(fn() => Carbon::today()->format('Y-m-d'))
                            ->visibleOn('edit'),

                    ])->columns(),
                Section::make('Comprobantes')
                    ->schema([
                        Forms\Components\Repeater::make('Comprobante')
                            ->relationship('documentos')
                            ->label('')
                            ->schema([
                                Forms\Components\Placeholder::make('Ndocumento')
                                    ->label('N° Documento')
                                    ->content(fn($record) => $record->Nombre ?? ''),
                                Forms\Components\Placeholder::make('FechaPago')
                                    ->label('Fecha de Pago')
                                    ->content(fn($record) => Carbon::parse($record->FechaPago ?? '')->format('d/m/Y')),
                                Forms\Components\FileUpload::make('Path')
                                    ->label('Archivo Comprobante')
                                    ->required()
                                    ->disk('public')
                                    ->directory('comprobantesCuotas')
                                    ->deletable(false)
                                    ->previewable()
                                    ->downloadable()
                                    ->columnSpanFull(),
                            ])
                            ->defaultItems(0)
                            ->columns()
                            ->grid(),
                    ])->visibleOn(['edit', 'view']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.persona.Rut')
                    ->label('Rut')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('user.name')->label('Persona')
                    ->label('Nombre')
                    ->searchable(),
                TextColumn::make('FechaPeriodo')
                    ->label('Periodo')
                    ->date('m/Y')
                    ->sortable(),
                TextColumn::make('FechaVencimiento')
                    ->label('Fecha Vencimiento')
                    ->date('d/m/Y'),

                TextColumn::make('TipoCuota')
                ->formatStateUsing(fn ($state) => match ($state) {
                    'cuota_ordinaria' => 'Cuota Ordinaria',
                    'cuota_extraordinaria' => 'Cuota Extraordinaria',
                    default => ucwords(str_replace('_', ' ', strtolower($state))),
                }),

                Tables\Columns\TextColumn::make('estadocuota.Estado')
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'Pendiente' => 'badgeAlert',
                        'Aprobado' => 'success',
                        'Rechazado' => 'danger',
                        'Cancelado' => 'danger',
                        'Pendiente Aprobacion' => 'warning',
                        default => 'gray',
                    })
                    ->label('Estado'),
                /*TextColumn::make('Monto')
                    ->label('Monto')
                    ->prefix("$")
                    ->money('CLP')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->prefix('$')
                            ->money('CLP')
                            ->label('Total')
                    ]),*/
                TextColumn::make('Pendiente')
                    ->money('CLP', locale: 'es_CL')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('CLP', locale: 'es_CL')
                            ->label('Total')
                    ]),
                TextColumn::make('Recaudado')
                    ->money('CLP', locale: 'es_CL')
                    ->summarize([
                        Tables\Columns\Summarizers\Sum::make()
                            ->money('CLP', locale: 'es_CL')
                            ->label('Total')
                    ]),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('idUsuario')
                    ->relationship('user', 'name')
                    ->searchable()
            ])
            ->headerActions([
                Tables\Actions\Action::make('importarCuotas')
                    ->label('Importar Cuotas')
                    ->icon('heroicon-o-arrow-up-tray')
                    ->form([
                        FileUpload::make('archivo_csv')
                            ->label('Archivo CSV')
                            ->disk('local')
                            ->directory('temp')
                            ->acceptedFileTypes(['text/csv', 'application/csv', 'text/plain'])
                            ->required(),
                    ])
                    ->action(function (array $data) {
                        $path = Storage::disk('local')->path($data['archivo_csv']);
                        $handle = fopen($path, 'r');

                        // Leer cabecera
                        $header = fgetcsv($handle, 1000, ',');

                        $count = 0;
                        $errors = [];

                        while (($row = fgetcsv($handle, 1000, ',')) !== FALSE) {
                            if (count($row) < 4) continue;

                            $email = trim($row[0]);
                            $periodo = trim($row[1]); // Esperado: Y-m-d o similar
                            $monto = trim($row[2]);
                            $estadoNombre = trim($row[3]);

                            $user = User::where('email', $email)->first();

                            if (!$user) {
                                $errors[] = "Usuario con email {$email} no encontrado.";
                                continue;
                            }

                            $estado = CuotasEstados::where('Estado', $estadoNombre)->first();
                            if (!$estado) {
                                $errors[] = "Estado '{$estadoNombre}' no válido para el email {$email}.";
                                continue;
                            }

                            try {
                                $fechaPeriodo = Carbon::parse($periodo);
                            } catch (\Exception $e) {
                                $errors[] = "Fecha '{$periodo}' inválida para el email {$email}.";
                                continue;
                            }

                            // Determinar TipoCuota por defecto o intentar inferir
                            // Según form(), se busca en PrecioCuotas por TipoVoluntario
                            $tipoVoluntario = $user->persona->TipoVoluntario ?? null;
                            $tipoCuota = 'cuota_ordinaria'; // Valor por defecto

                            if ($tipoVoluntario) {
                                $pc = PrecioCuotas::where('TipoVoluntario', $tipoVoluntario)
                                    ->where('Monto', '>', 0)
                                    ->first();
                                if ($pc) {
                                    $tipoCuota = $pc->TipoCuota;
                                }
                            }

                            Cuota::create([
                                'idUser' => $user->id,
                                'FechaPeriodo' => $fechaPeriodo->format('Y-m-d'),
                                'FechaVencimiento' => $fechaPeriodo->copy()->endOfMonth()->format('Y-m-d'),
                                'Monto' => $monto,
                                'Pendiente' => $estadoNombre === 'Pendiente' ? $monto : 0,
                                'Recaudado' => $estadoNombre === 'Aprobado' ? $monto : 0,
                                'Estado' => $estado->id,
                                'TipoCuota' => $tipoCuota,
                            ]);

                            $count++;
                        }

                        fclose($handle);
                        Storage::disk('local')->delete($data['archivo_csv']);

                        if ($count > 0) {
                            Notification::make()
                                ->title('Importación completada')
                                ->body("Se han importado {$count} cuotas correctamente.")
                                ->success()
                                ->send();
                        }

                        if (count($errors) > 0) {
                            Notification::make()
                                ->title('Errores en la importación')
                                ->body(implode('<br>', array_slice($errors, 0, 5)) . (count($errors) > 5 ? '<br>...' : ''))
                                ->danger()
                                ->persistent()
                                ->send();
                        }
                    }),
                Tables\Actions\ExportAction::make()
                    ->modalContent(view("filament.cuotas-exporter-modal"))
                    ->exporter(CuotasExporter::class)
                    ->fileDisk("public")
                    ->columnMapping(false)
                    ->color('primary'),

            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->button()
                    ->color('info'),

                Tables\Actions\Action::make('VerComprobante')
                    ->label('Recibo')
                    ->url(fn($record) => route('comprobante-cuota', $record->idDocumento))
                    ->openUrlInNewTab()
                    ->button()
                    ->visible(fn($record) => $record->Estado == 2 && $record->idDocumento)
                    ->color('success')
                    ->icon('heroicon-s-document-text'),

                Tables\Actions\Action::make('DescargarPDF')
                    ->label('PDF')
                    ->url(fn($record) => route('descargar-comprobante', $record->idDocumento))
                    ->openUrlInNewTab()
                    ->button()
                    ->visible(fn($record) => $record->Estado == 2 && $record->idDocumento)
                    ->color('primary')
                    ->icon('heroicon-o-arrow-down-tray'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                    Tables\Actions\ExportBulkAction::make()
                        ->modalContent(view("filament.cuotas-exporter-modal"))
                        ->exporter(CuotasExporter::class)
                        ->fileDisk("public")
                        ->columnMapping(false)
                        ->color('primary'),
                ]),
            ])
            ->groups([
                Tables\Grouping\Group::make('user.name')
                    ->label('Nombre'),
                Tables\Grouping\Group::make('estadocuota.Estado')
                    ->label('Estado')
            ])
            ->defaultGroup('user.name')
            ->defaultSort(fn($query) => $query->orderBy('idUser', 'desc')->orderBy('FechaPeriodo', 'asc'))
            ->paginated(25);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTesorerias::route('/'),
            'create' => Pages\CreateTesoreria::route('/create'),
            'edit' => Pages\EditTesoreria::route('/{record}/edit'),
        ];
    }
}
