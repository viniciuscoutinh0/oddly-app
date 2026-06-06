<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Enums\Pool\Visibility;
use App\Models\Competition;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Locked;
use Livewire\Form;

final class PoolForm extends Form
{
    #[Locked]
    public ?Competition $competition = null;

    public string $name = '';

    public ?string $description = null;

    public ?int $competition_id = null;

    public ?int $season_id = null;

    public ?Visibility $visibility = null;

    public int $points_exact = 10;

    public int $points_result = 5;

    public int $points_champion = 25;

    public int $points_group_position = 3;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:60'],
            'description' => ['nullable', 'string', 'max:300'],
            'competition_id' => ['required', 'integer', Rule::exists('competitions', 'id')],
            'season_id' => ['required', 'integer', Rule::exists('seasons', 'id')],
            'visibility' => ['required', Rule::enum(Visibility::class)],
            'points_exact' => ['required', 'integer', 'min:0'],
            'points_result' => ['required', 'integer', 'min:0'],
            'points_champion' => ['required', 'integer', 'min:0'],
            'points_group_position' => ['required', 'integer', 'min:0'],
        ];
    }

    public function setCompetition(?Competition $competition = null): void
    {
        $this->competition = $competition;

        $this->competition_id = $competition?->id;
    }

    public function updatedCompetitionId(?int $value = null): void
    {
        if ($value === null) {
            return;
        }

        $this->reset('season_id');

        $this->competition = Competition::query()
            ->find($value, [
                'id',
                'name',
                'code',
            ]);
    }
}
