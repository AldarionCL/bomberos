<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudesLicenciaResource\Pages;
use App\Filament\Resources\SolicitudesLicenciaResource\RelationManagers;
use App\Models\DocumentosTipo;
use App\Models\Solicitud;
use App\Models\SolicitudesLicencia;
use Filament\Forms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Soliciud')
                    ->schema([
                        Forms\Components\Select::make('SolicitadoPor')
                            ->relationship('solicitante', 'name')
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
                        Forms\Components\Select::make('TipoSolicitud')
                            ->options([
                                1 => 'Ingreso',
                                2 => 'Baja',
                                3 => 'Permiso'
                            ])
                            ->default(2)
                            ->hidden(),

                    ])->columns(),

                Forms\Components\Section::make('Datos del Voluntario')
                    ->schema([
                        Forms\Components\Select::make('AsociadoA')
                            ->relationship('asociado', 'name')
                            ->live()
                            ->label('Voluntario')
                            ->searchable()
                            ->hint('Seleccione un Voluntario para asociar a esta solicitud')
                            ->required(),
                        Forms\Components\RichEditor::make('Observaciones')
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
                                    ->options(fn() => DocumentosTipo::where('Clasificacion', 'privado')->pluck('Tipo', 'id')),
                                TextInput::make('Nombre'),
                                Forms\Components\FileUpload::make('Path')
                                    ->inlineLabel(true)
                                    ->label('Archivo')
                                    ->disk('public')
                                    ->downloadable()
                                    ->previewable()
                                    ->deletable(false)
                                    ->directory('documentos')
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
                return $query->where('TipoSolicitud', 3);
            })
            ->columns([
                TextColumn::make('id')->label('ID'),
                Tables\Columns\ImageColumn::make('aprobador.persona.Foto')
                    ->defaultImageUrl(url('/storage/fotosPersonas/placeholderAvatar.png'))
                    ->circular()
                    ->grow(false)
                    ->label('Foto'),
                TextColumn::make('asociado.name')->label('Nombre')
                    ->searchable(),
                Tables\Columns\TextColumn::make('Estado')
                    ->state(fn($record) => ($record->Estado === 0) ? 'Pendiente' : 'Aprobado')
                    ->badge()
                    ->color(fn($state) => $state == 'Aprobado' ? 'success' : 'danger')
                    ->icon(fn($state) => $state == 'Aprobado' ? 'heroicon-s-check' : 'heroicon-o-clock')
                    ->label('Estado'),
                TextColumn::make('solicitante.name')->label('Solicitado por:'),
                TextColumn::make('Fecha_registro')
                    ->label('Fecha de Solicitud')
                    ->date('d/m/Y'),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
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
