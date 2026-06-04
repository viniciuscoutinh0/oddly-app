<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Tournament\Resources\Competitions\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns;
use Filament\Tables\Table;

final class CompetitionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                Columns\TextColumn::make('name')
                    ->label('Nome')
                    ->searchable()
                    ->sortable(),

                Columns\TextColumn::make('code')
                    ->label('Código')
                    ->badge(),

                Columns\TextColumn::make('type')
                    ->label('Tipo')
                    ->badge(),

                Columns\TextColumn::make('seasons_count')
                    ->label('Edições')
                    ->counts('seasons')
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
