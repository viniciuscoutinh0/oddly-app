<?php

declare(strict_types=1);

namespace App\Enums\Fixture;

enum Status: string
{
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Finished = 'finished';
    case Postponed = 'postponed';
}
