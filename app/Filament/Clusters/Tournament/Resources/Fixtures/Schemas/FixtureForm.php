<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Tournament\Resources\Fixtures\Schemas;

use App\Enums\Fixture\Duration;
use App\Enums\Fixture\Status;
use App\Models\Stage;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

final class FixtureForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Section::make('Confronto')
                    ->columns(2)
                    ->schema([
                        Select::make('stage_id')
                            ->label('Fase')
                            ->relationship('stage', 'id')
                            ->getOptionLabelFromRecordUsing(
                                fn (Stage $record): string => "{$record->season->name} - {$record->name->getLabel()}",
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),

                        Select::make('home_team_id')
                            ->label('Mandante')
                            ->relationship('homeTeam', 'name')
                            ->searchable()
                            ->preload(),

                        Select::make('away_team_id')
                            ->label('Visitante')
                            ->relationship('awayTeam', 'name')
                            ->searchable()
                            ->preload(),

                        TextInput::make('group_letter')
                            ->label('Grupo')
                            ->maxLength(1),

                        TextInput::make('match_day')
                            ->label('Rodada')
                            ->numeric()
                            ->minValue(1),
                    ]),

                Section::make('Agenda')
                    ->columns(3)
                    ->schema([
                        DateTimePicker::make('match_date')
                            ->label('Data do Jogo')
                            ->seconds(false)
                            ->required(),

                        DateTimePicker::make('locked_at')
                            ->label('Trava Palpites em')
                            ->seconds(false),

                        Select::make('status')
                            ->label('Situação')
                            ->options(Status::class)
                            ->default(Status::Scheduled)
                            ->required(),
                    ]),

                Section::make('Placar')
                    ->columns(3)
                    ->schema([
                        Select::make('duration')
                            ->label('Decisão')
                            ->options(Duration::class)
                            ->default(Duration::Regular)
                            ->required()
                            ->columnSpanFull(),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('home_score')
                                    ->label('Mandante')
                                    ->numeric()
                                    ->minValue(0),

                                TextInput::make('away_score')
                                    ->label('Visitante')
                                    ->numeric()
                                    ->minValue(0),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('home_score_et')
                                    ->label('Mandante (Prorrog.)')
                                    ->numeric()
                                    ->minValue(0),

                                TextInput::make('away_score_et')
                                    ->label('Visitante (Prorrog.)')
                                    ->numeric()
                                    ->minValue(0),
                            ]),

                        Grid::make(2)
                            ->schema([
                                TextInput::make('home_score_pen')
                                    ->label('Mandante (Pên.)')
                                    ->numeric()
                                    ->minValue(0),

                                TextInput::make('away_score_pen')
                                    ->label('Visitante (Pên.)')
                                    ->numeric()
                                    ->minValue(0),
                            ]),
                    ]),

                TextInput::make('external_id')
                    ->label('ID Externo')
                    ->numeric()
                    ->unique(ignoreRecord: true),
            ]);
    }
}
