<?php

namespace App\Filament\Resources\Teams\Schemas;

use Filament\Forms\Components;
use Filament\Schemas;
use Filament\Schemas\Components\Utilities\Set;

class TeamForm
{
    public static function configure(Schemas\Schema $schema): Schemas\Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Schemas\Components\Grid::make(3)
                    ->schema([
                        Components\TextInput::make('name')
                            ->label('Nome')
                            ->required()
                            ->maxLength(30)
                            ->live(onBlur: true)
                            ->columnSpan(2)
                            ->afterStateUpdated(function (Set $set, ?string $state = null): void {
                                if (! $state) {
                                    return;
                                }

                                $short = mb_strtoupper(mb_substr($state, 0, 3));

                                $set('short_name', $short, '');
                            }),

                        Components\TextInput::make('short_name')
                            ->label('Nome Abreviado')
                            ->required()
                            ->maxLength(3),
                    ]),

                Components\TextInput::make('logo_url')
                    ->label('Logo')
                    ->required()
                    ->url(),
            ]);
    }
}
