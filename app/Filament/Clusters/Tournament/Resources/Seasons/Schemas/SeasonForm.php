<?php

namespace App\Filament\Clusters\Tournament\Resources\Seasons\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SeasonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('competition_id')
                    ->relationship('competition', 'name')
                    ->required(),
                DatePicker::make('start_date')
                    ->required(),
                DatePicker::make('end_date'),
                Select::make('winner_id')
                    ->relationship('winner', 'name'),
                TextInput::make('external_id')
                    ->numeric(),
            ]);
    }
}
