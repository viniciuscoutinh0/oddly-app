<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Fixture;
use Illuminate\Console\Command;

final class FixHourFixtureCommand extends Command
{
    protected $signature = 'app:fix-hour-fixture-command';

    protected $description = 'Fix Fixtures timezone to America/Sao_Paulo';

    public function handle(): int
    {
        $fixtures = Fixture::query()->get(['id', 'match_date']);

        foreach ($fixtures as $fixture) {
            $hour = $fixture->match_date->subHours(3);

            $fixture->update([
                'match_date' => $hour,
                'locked_at' => $hour->subMinutes(30),
            ]);

            $this->info("Fixture: {$fixture->id} moved to {$hour->format('d/m/Y H:i:s')}");
        }

        $this->info('All fixtures fixed match hour');

        return Command::SUCCESS;
    }
}
