<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\ChampionBet;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChampionBet>
 */
final class ChampionBetFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'season_id' => Season::factory(),
            'team_id' => Team::factory(),
            'is_correct' => null,
            'resolved_at' => null,
        ];
    }
}
