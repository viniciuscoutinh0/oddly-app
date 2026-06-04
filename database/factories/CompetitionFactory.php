<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Enums\Competition\Type;
use App\Models\Competition;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Competition>
 */
final class CompetitionFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->words(2, true),
            'code' => Str::upper(fake()->unique()->lexify('???')),
            'type' => fake()->randomElement(Type::cases()),
            'external_id' => fake()->unique()->numberBetween(1, 999999),
        ];
    }
}
