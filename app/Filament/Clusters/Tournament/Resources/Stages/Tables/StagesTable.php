<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Tournament\Resources\Stages\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

final class StagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('season.name')
                    ->label('Temporada')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Fase')
                    ->badge()
                    ->sortable(),

                TextColumn::make('sort_order')
                    ->label('Ordem')
                    ->numeric()
                    ->sortable(),

                IconColumn::make('is_knockout')
                    ->label('Mata-Mata')
                    ->boolean(),

                TextColumn::make('fixtures_count')
                    ->label('Jogos')
                    ->counts('fixtures')
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
