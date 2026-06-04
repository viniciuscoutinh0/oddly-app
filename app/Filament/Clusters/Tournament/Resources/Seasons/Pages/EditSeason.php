<?php

namespace App\Filament\Clusters\Tournament\Resources\Seasons\Pages;

use App\Filament\Clusters\Tournament\Resources\Seasons\SeasonResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSeason extends EditRecord
{
    protected static string $resource = SeasonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
