<?php

declare(strict_types=1);

namespace App\Filament\Clusters\Tournament\Resources\Competitions;

use App\Filament\Clusters\Tournament\Resources\Competitions\Pages\CreateCompetition;
use App\Filament\Clusters\Tournament\Resources\Competitions\Pages\EditCompetition;
use App\Filament\Clusters\Tournament\Resources\Competitions\Pages\ListCompetitions;
use App\Filament\Clusters\Tournament\Resources\Competitions\RelationManagers\SeasonsRelationManager;
use App\Filament\Clusters\Tournament\Resources\Competitions\Schemas\CompetitionForm;
use App\Filament\Clusters\Tournament\Resources\Competitions\Tables\CompetitionsTable;
use App\Filament\Clusters\Tournament\TournamentCluster;
use App\Models\Competition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

final class CompetitionResource extends Resource
{
    protected static ?string $model = Competition::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = TournamentCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return CompetitionForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompetitionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            SeasonsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCompetitions::route('/'),
            'create' => CreateCompetition::route('/create'),
            'edit' => EditCompetition::route('/{record}/edit'),
        ];
    }
}
