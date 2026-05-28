<?php

namespace App\Filament\Clusters\Tournament\Resources\Stages\RelationManagers;

use App\Filament\Clusters\Tournament\Resources\Fixtures\FixtureResource;
use App\Filament\Clusters\Tournament\Resources\Stages\StageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class FixturesRelationManager extends RelationManager
{
    protected static string $relationship = 'fixtures';

    protected static ?string $relatedResource = FixtureResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
