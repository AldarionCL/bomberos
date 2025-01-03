<?php

namespace App\Filament\Resources\SolicitudesBajaResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Auth;

class AprobacionesRelationManager extends RelationManager
{
    protected static string $relationship = 'aprobaciones';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('idAprobador')
                    ->relationship('aprobador','name')
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('Aprobadores')
            ->columns([
                Tables\Columns\TextColumn::make('aprobador.name'),
                Tables\Columns\ToggleColumn::make('Estado')
                    ->onColor('success')
                    ->onIcon('heroicon-s-check')
                    ->offColor('danger')
                    ->offIcon('heroicon-s-x-mark')
                ->disabled(fn ($record) => ($record->idAprobador == Auth::user()->id) ? false : true),
            ])
            ->filters([
                //
            ])
            ->headerActions([
//                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()->hidden(fn($record) => !Auth::user()->isRole('admin')),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
