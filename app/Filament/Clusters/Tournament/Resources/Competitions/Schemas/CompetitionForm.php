<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Tournament\Resources\Competitions\Schemas;

use App\Enums\Competition\Type;
use Filament\Forms\Components;
use Filament\Schemas;

final class CompetitionForm
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
                            ->maxLength(60)
                            ->columnSpan(2)
                            ->live(onBlur: true)
                            ->afterStateUpdatedJS(<<<'JS'
                                    $set('code', $get('name')?.toUpperCase().substring(0, 3));
                                JS),

                        Components\TextInput::make('code')
                            ->label('Código')
                            ->required()
                            ->maxLength(3)
                            ->unique(ignoreRecord: true),
                    ]),

                Components\Select::make('type')
                    ->label('Tipo')
                    ->required()
                    ->options(Type::class),
            ]);
    }
}
