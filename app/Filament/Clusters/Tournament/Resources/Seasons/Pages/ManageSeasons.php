<?php

namespace App\Filament\Clusters\Tournament\Resources\Seasons\Pages;

use App\Filament\Clusters\Tournament\Resources\Seasons\SeasonResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;

class ManageSeasons extends ManageRecords
{
    protected static string $resource = SeasonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver()
                ->modalWidth(Width::Large),
        ];
    }
}
