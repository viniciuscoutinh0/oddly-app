<?php

namespace App\Filament\Clusters\Tournament\Resources\Stages\Pages;

use App\Filament\Clusters\Tournament\Resources\Stages\StageResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Enums\Width;

class ManageStages extends ManageRecords
{
    protected static string $resource = StageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->slideOver()
                ->modalWidth(Width::Large),
        ];
    }
}
