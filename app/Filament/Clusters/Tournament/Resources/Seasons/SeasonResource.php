<?php

namespace App\Filament\Clusters\Tournament\Resources\Seasons;

use App\Filament\Clusters\Tournament\Resources\Seasons\Pages\CreateSeason;
use App\Filament\Clusters\Tournament\Resources\Seasons\Pages\EditSeason;
use App\Filament\Clusters\Tournament\Resources\Seasons\Pages\ListSeasons;
use App\Filament\Clusters\Tournament\Resources\Seasons\RelationManagers\StagesRelationManager;
use App\Filament\Clusters\Tournament\Resources\Seasons\Schemas\SeasonForm;
use App\Filament\Clusters\Tournament\Resources\Seasons\Tables\SeasonsTable;
use App\Filament\Clusters\Tournament\TournamentCluster;
use App\Models\Season;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SeasonResource extends Resource
{
    protected static ?string $model = Season::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = TournamentCluster::class;

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $schema): Schema
    {
        return SeasonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SeasonsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            StagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSeasons::route('/'),
            'create' => CreateSeason::route('/create'),
            'edit' => EditSeason::route('/{record}/edit'),
        ];
    }
}
