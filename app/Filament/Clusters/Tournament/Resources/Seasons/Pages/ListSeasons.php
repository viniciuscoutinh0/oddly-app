<?php

namespace App\Filament\Clusters\Tournament\Resources\Seasons\Pages;

use App\Filament\Clusters\Tournament\Resources\Seasons\SeasonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeasons extends ListRecords
{
    protected static string $resource = SeasonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
