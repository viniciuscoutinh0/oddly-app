<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Tournament\Resources\Stages\Schemas;

use App\Enums\Stage\Name;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

final class StageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('season_id')
                    ->label('Temporada')
                    ->relationship('season', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record): string => $record->name)
                    ->searchable()
                    ->preload()
                    ->required(),

                Select::make('name')
                    ->label('Fase')
                    ->options(Name::class)
                    ->required()
                    ->live()
                    ->afterStateUpdated(fn (Set $set, ?string $state) => $set(
                        'is_knockout',
                        $state !== null && Name::from($state)->isKnockout(),
                    )),

                TextInput::make('sort_order')
                    ->label('Ordem')
                    ->required()
                    ->numeric()
                    ->minValue(1),

                Toggle::make('is_knockout')
                    ->label('Mata-Mata?'),
            ]);
    }
}
