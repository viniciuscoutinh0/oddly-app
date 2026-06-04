<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Fixture\Duration;
use App\Enums\Fixture\Status;
use App\Models\Fixture;
use App\Models\Stage;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fixture>
 */
final class FixtureFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'stage_id' => Stage::factory(),
            'home_team_id' => Team::factory(),
            'away_team_id' => Team::factory(),
            'home_score' => null,
            'away_score' => null,
            'duration' => Duration::Regular,
            'group_letter' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'match_day' => fake()->numberBetween(1, 3),
            'match_date' => fake()->dateTimeBetween('-1 week', '+1 month'),
            'status' => Status::Scheduled,
            'external_id' => fake()->unique()->numberBetween(1, 999999),
        ];
    }
}
