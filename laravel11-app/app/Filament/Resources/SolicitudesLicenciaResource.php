<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudesLicenciaResource\Pages;
use App\Filament\Resources\SolicitudesLicenciaResource\RelationManagers;
use App\Models\DocumentosTipo;
use App\Models\Solicitud;
use App\Models\SolicitudesLicencia;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Support\Enums\Alignment;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class SolicitudesLicenciaResource extends Resource
{
    protected static ?string $model = Solicitud::class;

    protected static ?string $navigationIcon = 'heroicon-s-exclamation-triangle';
    protected static ?string $navigationGroup = 'Personal';
    protected static ?string $navigationLabel = 'Solicitudes Licencia';

    protected static ?string $label = 'Solicitud Licencia';
    protected static ?string $pluralLabel = 'Solicitudes Licencias';
    protected static ?int $navigationSort = 2;

    public static function canAccess(): bool
    {
        return true;
    }

    public static function canEdit(Model $record): bool
    {
        return true;
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canDelete(Model $record): bool
    {
        return $record->Estado == 0 && (Auth::user()->isRole('Administrador') || $record->SolicitadoPor == Auth::user()->id);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Group::make([
                    Forms\Components\Section::make('Periodo de Licencia')
                        ->schema([
                            Forms\Components\DatePicker::make('FechaDesde')
                                ->reactive()
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    if ($state) {
                                        $fechaDesde = Carbon::parse($state);
                                        if ($get('FechaHasta')) {
                                            $fechaHasta = Carbon::parse($get('FechaHasta'));
                                            $dias = $fechaDesde->diffInDays($fechaHasta) + 1;
                                            for ($i = 0; $i <= $dias; $i++) {
                                                $fecha = $fechaDesde->copy()->addDays($i);
                                                if ($fecha->isWeekend()) {
                                                    $dias--;
                                                }
                                            }
                                            $set('DiasHabiles', $dias);

                                            if (!Solicitud::verificaDiasDisponibles($get('AsociadoA'), $dias, $get('TipoSolicitud'))) {
                                                Notification::make()
                                                    ->title('Error')
                                                    ->body('El rango de dias solicitados supera los 30 dias disponibles')
                                                    ->icon('heroicon-o-x-circle')
                                                    ->danger()
                                                    ->send();
                                                $set('NoGuarda', true);

                                            }
                                        }
                                    }
                                })
                                ->label('Fecha Desde')
                                ->required(),
                            Forms\Components\DatePicker::make('FechaHasta')
                                ->live()
                                ->afterStateUpdated(function ($state, $set, $get) {
                                    if ($get('FechaDesde')) {
                                        $fechaDesde = Carbon::parse($get('FechaDesde'));
                                        if ($state) {
                                            $fechaHasta = Carbon::parse($state);
                                            $dias = $fechaDesde->diffInDays($fechaHasta) + 1;
                                            for ($i = 0; $i <= $dias; $i++) {
                                                $fecha = $fechaDesde->copy()->addDays($i);
                                                if ($fecha->isWeekend()) {
                                                    $dias--;
                                                }
                                            }
                                            $set('DiasHabiles', $dias);

                                            if (!Solicitud::verificaDiasDisponibles($get('AsociadoA'), $dias, $get('TipoSolicitud'))) {
                                                Notification::make()
                                                    ->title('Error')
                                                    ->body('El rango de dias solicitados supera los dias disponibles')
                                                    ->icon('heroicon-o-x-circle')
                                                    ->danger()
                                                    ->send();
                                                $set('NoGuarda', true);
                                            }
                                        }
                                    }
                                })
                                ->label('Fecha Hasta')
                                ->required(),
                            TextInput::make('DiasHabiles')
                                ->live()
                                ->label('Total Dias')
                                ->readOnly()
                                ->required(),

                            Forms\Components\Select::make('TipoSolicitud')
                                ->options(fn() => \App\Models\SolicitudesTipo::whereIn('id', [3, 4])->pluck('Tipo', 'id'))
                                ->default(3)
                                ->hint('Una licencia, tiene un plazo maximo de 30 dias (trimestral), mientras que una extendida, tiene un plazo maximo de 6 meses')
                                ->reactive()
                                ->required()
                                ->columnSpanFull(),

                        ])->columns(3),
                    Forms\Components\Section::make('Solicitante')
                        ->schema([
                            Forms\Components\Select::make('SolicitadoPor')
                                ->options(fn() => \App\Models\User::whereHas('persona', function ($query) {
                                    $query->where('Activo', 1);
                                })->pluck('name', 'id'))
//                            ->relationship('solicitante', 'name')
                                ->disabled(fn($record) => !Auth::user()->isRole('Administrador'))
                                ->label('Solicitado por')
                                ->default(Auth::user()->id),
                            Forms\Components\Select::make('Estado')
                                ->options([
                                    0 => 'Pendiente',
                                    1 => 'Aprobado',
                                    2 => 'Cancelado',
                                ])->default(0)
                                ->disabled(fn($record) => !Auth::user()->isRole('Administrador')),
                        ])->columns(),
                ])->columns(),
                Forms\Components\Group::make([
                    Forms\Components\Section::make('Datos del Voluntario')
                        ->schema([
                            Forms\Components\Select::make('AsociadoA')
//                            ->relationship('asociado', 'name')
                                ->options(fn() => \App\Models\User::whereHas('persona', function ($query) {
                                    $query->where('Activo', 1);
                                    if (!Auth::user()->isRole('Administrador') && !Auth::user()->isCargo('Director') && !Auth::user()->isCargo('Capitan')  && !Auth::user()->isCargo('Capitán')) {
                                        $query->where('id', Auth::user()->id);
                                    }
                                })->pluck('name', 'id'))
                                ->live()
                                ->label('Nombre')
                                ->searchable()
                                ->hint('Seleccione un Voluntario para asociar a esta solicitud')
                                ->required()
                                ->default(fn() => Auth::user()->id)
                            ->visibleOn('create'),
                            Forms\Components\Placeholder::make('AsociadoA_Placeholder')
                                ->content(fn($record) => $record->asociado->name)
                                ->label('Voluntario')
                                ->visibleOn('edit'),

                            Forms\Components\RichEditor::make('Observaciones')
                        ]),

                ]),

                Forms\Components\Section::make('Documentos')
                    ->schema([
                        Forms\Components\Repeater::make('DocumentosRepeater')
                            ->label('')
                            ->addActionLabel('Agregar Documento')
                            ->relationship('documentos')
                            ->deletable(false)
                            ->schema([
                                Select::make('TipoDocumento')
                                    ->options(fn() => DocumentosTipo::where('Clasificacion', 'privado')->pluck('Tipo', 'id'))
                                    ->required(),
                                TextInput::make('Nombre')
                                    ->required(),
                                Forms\Components\FileUpload::make('Path')
                                    ->inlineLabel(true)
                                    ->label('Archivo')
                                    ->disk('public')
                                    ->downloadable()
                                    ->previewable()
                                    ->deletable(false)
                                    ->directory('documentos')
                                    ->required()
                                    ->inlineLabel(false),
                            ])->columns(3)
                            ->mutateRelationshipDataBeforeCreateUsing(function (array $data, $get): array {
                                $data['AsociadoA'] = $get('AsociadoA');
                                return $data;
                            })
                            ->mutateRelationshipDataBeforeSaveUsing(function (array $data, $get): array {
                                $data['AsociadoA'] = $get('AsociadoA');
                                return $data;
                            })
                            ->defaultItems(0),

                    ])->compact(),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                if (!Auth::user()->isRole('Administrador') && !Auth::user()->isCargo('Director') && !Auth::user()->isCargo('Capitan') && !Auth::user()->isCargo('Capitán')) {
                    $query->whereHas('asociado', function ($query) {
                        $query->where('id', Auth::user()->id);
                    });
                }
                return $query->whereIn('TipoSolicitud', [3, 4]);
            })
            ->columns([
                Tables\Columns\Layout\Split::make([
                    /*TextColumn::make('id')
                        ->description('ID', position: 'above')
                        ->label('ID'),*/

                    Tables\Columns\Layout\Split::make([
                        Tables\Columns\ImageColumn::make('aprobador.persona.Foto')
                            ->defaultImageUrl(url('/storage/fotosPersonas/placeholderAvatar.png'))
                            ->circular()
                            ->grow(false)
                            ->label('Foto'),
                        TextColumn::make('asociado.name')
                            ->description('Nombre', position: 'above')
                            ->label('Nombre')
                            ->searchable(),
                    ]),

                    TextColumn::make('Fecha_registro')
                        ->description('Fecha solicitud', position: 'above')
                        ->label('Fecha de Solicitud')
                        ->date('d/m/Y'),
                    TextColumn::make('FechaDesde')
                        ->description('Fecha Desde', position: 'above')
                        ->label('Fecha desde')
                        ->date('d/m/Y'),
                    TextColumn::make('FechaHasta')
                        ->description('Fecha Hasta', position: 'above')
                        ->label('Fecha hasta')
                        ->date('d/m/Y'),
                    TextColumn::make('DiasHabiles')
                        ->description('Total Dias', position: 'above')
                        ->label('Total Dias')
                        ->sortable(),
                    TextColumn::make('tipo.Tipo')
                        ->description('Tipo Solicitud', position: 'above')
                        ->label('Tipo Solicitud'),

                    TextColumn::make('solicitante.name')
                        ->description('Solicitado por', position: 'above')
                        ->label('Solicitado por:'),

                    Tables\Columns\TextColumn::make('Estado')
                        ->state(fn($record) => ($record->Estado === 0) ? 'Pendiente' : (($record->Estado === 2) ? 'Cancelado' : 'Aprobado'))
                        ->badge()
                        ->color(fn($state) => $state == 'Aprobado' ? 'success' : 'danger')
                        ->icon(fn($state) => $state == 'Aprobado' ? 'heroicon-s-check' : 'heroicon-o-clock')
                        ->label('Estado'),
                ])
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                    ->button(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
//                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\AprobacionesRelationManager::class,
//            RelationManagers\DocumentosRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListSolicitudesLicencias::route('/'),
            'create' => Pages\CreateSolicitudesLicencia::route('/create'),
            'edit' => Pages\EditSolicitudesLicencia::route('/{record}/edit'),
        ];
    }
}
