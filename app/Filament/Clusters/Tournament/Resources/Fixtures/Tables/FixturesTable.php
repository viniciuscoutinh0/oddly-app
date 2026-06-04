<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Tournament\Resources\Fixtures\Tables;

use App\Enums\Fixture\Status;
use App\Models\Fixture;
use App\Models\Stage;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class FixturesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('match_date')
            ->columns([
                TextColumn::make('match_date')
                    ->label('Data')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),

                TextColumn::make('stage.name')
                    ->label('Fase')
                    ->badge()
                    ->sortable(),

                TextColumn::make('group_letter')
                    ->label('Grupo')
                    ->badge()
                    ->placeholder('-'),

                TextColumn::make('homeTeam.name')
                    ->label('Mandante')
                    ->placeholder('A definir')
                    ->searchable(),

                TextColumn::make('score')
                    ->label('Placar')
                    ->state(fn (Fixture $record): string => $record->home_score === null
                        ? 'vs'
                        : "{$record->home_score} - {$record->away_score}"),

                TextColumn::make('awayTeam.name')
                    ->label('Visitante')
                    ->placeholder('A definir')
                    ->searchable(),

                TextColumn::make('status')
                    ->label('Situação')
                    ->badge()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Situação')
                    ->options(Status::class),

                SelectFilter::make('stage')
                    ->label('Fase')
                    ->relationship('stage', 'name')
                    ->getOptionLabelFromRecordUsing(fn (Stage $record): string => $record->name->getLabel()),
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
