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
                    ->relationship('season', 'id')
                    ->required(),
                TextInput::make('name')
                    ->required(),
                TextInput::make('order')
                    ->required()
                    ->numeric(),
                Toggle::make('is_knockout')
                    ->required(),
            ]);
    }
}
