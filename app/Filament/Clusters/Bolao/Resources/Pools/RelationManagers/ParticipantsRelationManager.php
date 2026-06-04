<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Bolao\Resources\Pools\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;

final class ParticipantsRelationManager extends RelationManager
{
    protected static string $relationship = 'participants';

    protected static ?string $title = 'Participantes';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Jogador')->searchable(),
                TextColumn::make('email')->label('E-mail')->searchable(),
                TextColumn::make('joined_at')
                    ->label('Entrou em')
                    ->state(fn ($record): ?string => $record->pivot->joined_at
                        ? Carbon::parse($record->pivot->joined_at)->format('d/m/Y H:i')
                        : null),
            ]);
    }
}
