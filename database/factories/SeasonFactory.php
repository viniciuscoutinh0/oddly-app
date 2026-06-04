<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Competition;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Season>
 */
final class SeasonFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 year', '+1 year');

        return [
            'competition_id' => Competition::factory(),
            'start_date' => $start,
            'end_date' => (clone $start)->modify('+1 month'),
            'winner_id' => null,
            'external_id' => fake()->unique()->numberBetween(1, 999999),
        ];
    }
}
