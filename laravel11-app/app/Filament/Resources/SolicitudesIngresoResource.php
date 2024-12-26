<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudesIngresoResource\Pages;
use App\Filament\Resources\SolicitudesIngresoResource\RelationManagers;
use App\Models\Solicitud;
use App\Models\SolicitudesIngreso;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SolicitudesIngresoResource extends Resource
{
    protected static ?string $model = Solicitud::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-plus';
    protected static ?string $navigationGroup = 'Solicitudes';
    protected static ?string $navigationLabel = 'Solicitudes Ingreso';

    protected static ?string $label = 'Solicitud Ingreso';
    protected static ?string $pluralLabel = 'Solicitudes Ingresos';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
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
            'index' => Pages\ListSolicitudesIngresos::route('/'),
            'create' => Pages\CreateSolicitudesIngreso::route('/create'),
            'edit' => Pages\EditSolicitudesIngreso::route('/{record}/edit'),
        ];
    }
}
