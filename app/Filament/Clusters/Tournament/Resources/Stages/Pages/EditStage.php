<?php

namespace App\Filament\Clusters\Tournament\Resources\Stages\Pages;

use App\Filament\Clusters\Tournament\Resources\Stages\StageResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditStage extends EditRecord
{
    protected static string $resource = StageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
