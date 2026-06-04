<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Stage\Name;
use App\Models\Season;
use App\Models\Stage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Stage>
 */
final class StageFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->randomElement(Name::cases());

        return [
            'season_id' => Season::factory(),
            'name' => $name,
            'sort_order' => fake()->numberBetween(1, 7),
            'is_knockout' => $name->isKnockout(),
        ];
    }
}
