<?php

namespace App\Filament\Clusters\Tournament\Resources\Competitions\RelationManagers;

use App\Filament\Clusters\Tournament\Resources\Seasons\SeasonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;

class SeasonsRelationManager extends RelationManager
{
    protected static string $relationship = 'seasons';

    protected static ?string $relatedResource = SeasonResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make(),
            ]);
    }
}
