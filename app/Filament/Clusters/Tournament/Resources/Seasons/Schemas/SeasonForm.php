<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Tournament\Resources\Seasons\Schemas;

use Filament\Forms\Components;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;
use Illuminate\Support\Number;

final class SeasonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Components\Select::make('competition_id')
                    ->label('Competição')
                    ->relationship('competition', 'name')
                    ->required(),

                Components\FileUpload::make('logo_path')
                    ->label('Logo')
                    ->image()
                    ->maxSize(1024 * 1024)
                    ->directory('season-assets')
                    ->helperText(
                        fn (Components\FileUpload $component): string => 'Tamanho máximo permitido: '
                        .Number::fileSize(
                            $component->getMaxSize(),
                        ),
                    )
                    ->nullable(),

                Grid::make()
                    ->schema([
                        Components\DatePicker::make('start_date')
                            ->label('Date de Início')
                            ->required(),

                        Components\DatePicker::make('end_date')
                            ->label('Data de Termínio'),
                    ]),

                Components\Select::make('winner_id')
                    ->label('Campeão')
                    ->relationship('winner', 'name'),
            ]);
    }
}
