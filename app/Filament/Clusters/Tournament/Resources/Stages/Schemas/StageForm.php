<?php

namespace App\Filament\Clusters\Tournament\Resources\Stages\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class StageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('season_id')
                    ->label('Temporada')
                    ->relationship('season', 'id')
                    ->getOptionLabelFromRecordUsing(fn ($record) => $record->name)
                    ->required(),

                TextInput::make('name')
                    ->label('Nome')
                    ->required(),

                TextInput::make('order')
                    ->label('Ordem')
                    ->required()
                    ->numeric(),

                Toggle::make('is_knockout')
                    ->label('Mata-Mata?')
                    ->required(),
            ]);
    }
}
