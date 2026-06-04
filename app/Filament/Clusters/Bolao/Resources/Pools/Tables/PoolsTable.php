<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Bolao\Resources\Pools\Tables;

use App\Enums\Pool\Visibility;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

final class PoolsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nome')->searchable()->sortable(),
                TextColumn::make('season.name')->label('Temporada')->sortable(),
                TextColumn::make('owner.name')->label('Dono')->searchable(),
                TextColumn::make('visibility')->label('Visibilidade')->badge(),
                TextColumn::make('participants_count')->label('Participantes')->counts('participants')->sortable(),
                TextColumn::make('created_at')->label('Criado em')->dateTime('d/m/Y H:i')->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('visibility')->label('Visibilidade')->options(Visibility::class),
                SelectFilter::make('season')->label('Temporada')->relationship('season', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record): string => $record->name),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
