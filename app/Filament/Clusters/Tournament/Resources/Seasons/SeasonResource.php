<?php

namespace App\Filament\Clusters\Tournament\Resources\Seasons;

use App\Filament\Clusters\Tournament\Resources\Seasons\Pages\ManageSeasons;
use App\Filament\Clusters\Tournament\TournamentCluster;
use App\Models\Season;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns;
use Filament\Tables\Table;

class SeasonResource extends Resource
{
    protected static ?string $model = Season::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = TournamentCluster::class;

    protected static ?string $recordTitleAttribute = 'start_date';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                Components\Select::make('competition_id')
                    ->label('Competição')
                    ->relationship('competition', 'name')
                    ->required(),

                Components\DatePicker::make('start_date')
                    ->label('Data de Início')
                    ->required()
                    ->date()
                    ->minDate(today()),

                Components\DatePicker::make('end_date')
                    ->label('Data de Término')
                    ->required()
                    ->date()
                    ->after('start_date'),

                Components\Select::make('winner_id')
                    ->label('Campeão')
                    ->relationship('winner', 'name')
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('competition.name')
            ->columns([
                Columns\TextColumn::make('competition.name')
                    ->label('Competição')
                    ->searchable(),

                Columns\TextColumn::make('start_date')
                    ->label('Data de Início')
                    ->date('d/m/Y'),

                Columns\TextColumn::make('end_date')
                    ->label('Data de Término')
                    ->date('d/m/Y'),

                Columns\TextColumn::make('winner.name')
                    ->label('Campeão')
                    ->searchable()
                    ->placeholder('N/A'),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make()
                    ->slideOver()
                    ->modalWidth(Width::Large),

                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageSeasons::route('/'),
        ];
    }
}
