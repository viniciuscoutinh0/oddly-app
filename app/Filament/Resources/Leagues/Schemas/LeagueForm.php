<?php

namespace App\Filament\Resources\Leagues\Schemas;

use App\Enums\League\Type;
use App\Models\League;
use Filament\Forms\Components;
use Filament\Schemas\Schema;

class LeagueForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Components\TextInput::make('name')
                    ->label('Nome')
                    ->required()
                    ->unique(League::class, 'name', ignoreRecord: true)
                    ->maxLength(60),

                Components\Radio::make('type')
                    ->label('Tipo')
                    ->required()
                    ->options(Type::all()),

                Components\TextInput::make('logo')
                    ->label('Logo')
                    ->url()
                    ->required(),
            ]);
    }
}
