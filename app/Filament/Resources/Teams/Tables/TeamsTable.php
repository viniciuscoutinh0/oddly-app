<?php

namespace App\Filament\Resources\Teams\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns;
use Filament\Tables\Table;

class TeamsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Columns\ImageColumn::make('logo')
                    ->imageSize(32),

                Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable(),

                Columns\TextColumn::make('short_name')
                    ->label('Nome Reduzido'),

                Columns\TextColumn::make('tla')
                    ->label('Sigla'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
