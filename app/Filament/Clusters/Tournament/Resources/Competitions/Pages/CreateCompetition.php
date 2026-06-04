<?php

namespace App\Filament\Clusters\Tournament\Resources\Competitions\Pages;

use App\Filament\Clusters\Tournament\Resources\Competitions\CompetitionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCompetition extends CreateRecord
{
    protected static string $resource = CompetitionResource::class;
}
