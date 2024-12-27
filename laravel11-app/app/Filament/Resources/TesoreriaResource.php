<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TesoreriaResource\Pages;
use App\Filament\Resources\TesoreriaResource\RelationManagers;
use App\Models\Cuota;
use Coolsam\FilamentFlatpickr\Forms\Components\Flatpickr;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TesoreriaResource extends Resource
{
    protected static ?string $model = Cuota::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Tesoreria';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make()
                ->schema([
                    Select::make('idUser')
                    ->relationship('user', 'name')->label('Persona'),
//                    DatePicker::make('fechaPeriodo')->label('Fecha de Periodo'),
                    Flatpickr::make('FechaPeriodo')
                        ->label('Periodo Desde'),
                    Flatpickr::make('FechaVencimiento')
                        ->label('Fecha de Vencimiento'),
                    Select::make('Estado')
                        ->options([
                            1 => 'Pendiente',
                            2 => 'Aprobado',
                            3 => 'Rechazado',
                            4 => 'Cancelado',
                        ])->default(1)->label('Estado'),
                    Flatpickr::make('FechaPago')->label('Fecha de Pago'),
                    Forms\Components\TextInput::make('Documento')->label('N° Documento'),
                    Forms\Components\FileUpload::make('DocumentoArchivo')->label('Archivo'),

                ])->columns()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.persona.Rut')->label('Rut')->searchable(),
                TextColumn::make('user.name')->label('Persona')->searchable(),
                TextColumn::make('FechaPeriodo')->label('Periodo')->date('m-Y'),
                Tables\Columns\BadgeColumn::make('Estado')->label('Estado')
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
