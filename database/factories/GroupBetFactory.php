<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\GroupBet;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GroupBet>
 */
final class GroupBetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'season_id' => Season::factory(),
            'group_letter' => fake()->randomElement(['A', 'B', 'C', 'D']),
            'team_id' => Team::factory(),
            'predicted_position' => fake()->numberBetween(1, 2),
            'is_correct' => null,
            'resolved_at' => null,
        ];
    }
}
