<?php

namespace App\Filament\Resources;

use App\Filament\Resources\SolicitudesVacacionesResource\Pages;
use App\Filament\Resources\SolicitudesVacacionesResource\RelationManagers;
use App\Models\Solicitud;
use App\Models\SolicitudesVacaciones;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class SolicitudesVacacionesResource extends Resource
{
    protected static ?string $model = Solicitud::class;

    protected static ?string $navigationIcon = 'heroicon-s-globe-americas';
    protected static ?string $navigationGroup = 'Solicitudes';
    protected static ?string $navigationLabel = 'Solicitudes Vacaciones';

    protected static ?string $label = 'Solicitud Vacaciones';
    protected static ?string $pluralLabel = 'Solicitudes Vacaciones';

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
            'index' => Pages\ListSolicitudesVacaciones::route('/'),
            'create' => Pages\CreateSolicitudesVacaciones::route('/create'),
            'edit' => Pages\EditSolicitudesVacaciones::route('/{record}/edit'),
        ];
    }
}
