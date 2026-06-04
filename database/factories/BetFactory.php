<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Bet;
use App\Models\Fixture;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Bet>
 */
final class BetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'fixture_id' => Fixture::factory(),
            'home_score' => fake()->numberBetween(0, 4),
            'away_score' => fake()->numberBetween(0, 4),
            'is_exact' => null,
            'is_correct_result' => null,
            'resolved_at' => null,
        ];
    }
}
