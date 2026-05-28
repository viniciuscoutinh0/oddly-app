<?php

namespace App\Filament\Clusters\Tournament\Resources\Stages;

use App\Filament\Clusters\Tournament\Resources\Stages\Pages\CreateStage;
use App\Filament\Clusters\Tournament\Resources\Stages\Pages\EditStage;
use App\Filament\Clusters\Tournament\Resources\Stages\Pages\ListStages;
use App\Filament\Clusters\Tournament\Resources\Stages\RelationManagers\FixturesRelationManager;
use App\Filament\Clusters\Tournament\Resources\Stages\Schemas\StageForm;
use App\Filament\Clusters\Tournament\Resources\Stages\Tables\StagesTable;
use App\Filament\Clusters\Tournament\TournamentCluster;
use App\Models\Stage;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class StageResource extends Resource
{
    protected static ?string $model = Stage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $cluster = TournamentCluster::class;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return StageForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return StagesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            FixturesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListStages::route('/'),
            'create' => CreateStage::route('/create'),
            'edit' => EditStage::route('/{record}/edit'),
        ];
    }
}
