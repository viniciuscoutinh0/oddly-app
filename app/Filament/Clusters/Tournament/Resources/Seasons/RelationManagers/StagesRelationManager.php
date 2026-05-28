<?php

namespace App\Filament\Clusters\Tournament\Resources\Seasons\RelationManagers;

use App\Filament\Clusters\Tournament\Resources\Stages\StageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class StagesRelationManager extends RelationManager
{
    protected static string $relationship = 'stages';

    protected static ?string $relatedResource = StageResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
