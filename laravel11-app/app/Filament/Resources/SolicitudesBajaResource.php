<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudesBajaResource\Pages;
use App\Filament\Resources\SolicitudesBajaResource\RelationManagers;
use App\Models\Solicitud;
use App\Models\SolicitudesBaja;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SolicitudesBajaResource extends Resource
{
    protected static ?string $model = Solicitud::class;

    protected static ?string $navigationIcon = 'heroicon-s-user-minus';
    protected static ?string $navigationGroup = 'Solicitudes';
    protected static ?string $navigationLabel = 'Solicitudes Baja';

    protected static ?string $label = 'Solicitud Baja';
    protected static ?string $pluralLabel = 'Solicitudes Bajas';



    public static function form(Form $form): Form
    {
        return $form
            ->schema([

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
            'index' => Pages\ListSolicitudesBajas::route('/'),
            'create' => Pages\CreateSolicitudesBaja::route('/create'),
            'edit' => Pages\EditSolicitudesBaja::route('/{record}/edit'),
        ];
    }
}
