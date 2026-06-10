<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Pool\Visibility;
use App\Models\Pool;
use App\Models\Season;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Pool>
 */
final class PoolFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return [
            'name' => $name,
            'slug' => Str::slug($name).'-'.fake()->unique()->numberBetween(1, 999999),
            'description' => fake()->optional()->sentence(),
            'season_id' => Season::factory(),
            'owner_id' => User::factory(),
            'visibility' => Visibility::Private,
            'invite_code' => Str::upper(Str::random(8)),
            'points_exact' => 10,
            'points_result' => 5,
            'points_champion' => 25,
            'points_group_position' => 3,
        ];
    }

    public function public(): static
    {
        return $this->state(fn (): array => [
            'visibility' => Visibility::Public,
            'invite_code' => null,
        ]);
    }
}
