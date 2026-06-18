<?php

declare(strict_types=1);

namespace App\Livewire\Forms;

use App\Models\Pool;
use App\Models\PoolPrizeDistribution;
use Closure;
use Livewire\Form;

final class PrizeDistributionForm extends Form
{
    /** @var array<int, int> */
    public array $distributions = [];

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'distributions' => ['required', 'array', $this->whenDistributionIsValid()],
            'distributions.*' => ['required', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function setFromPool(Pool $pool): void
    {
        $this->distributions = $pool
            ->distributions
            ->mapWithKeys(fn (PoolPrizeDistribution $distribution): array => [
                $distribution->position => $distribution->percentage,
            ])
            ->all();
    }

    public function pruneEmpty(): void
    {
        $this->distributions = collect($this->distributions)
            ->reject(fn (?int $value): bool => blank($value) || $value === 0)
            ->all();
    }

    private function whenDistributionIsValid(): Closure
    {
        return function (string $_attribute, mixed $value, Closure $fail): void {
            if (! is_array($value) || $value === []) {
                return;
            }

            $positions = array_map('intval', array_keys($value));

            sort($positions);

            if ($positions !== range(1, count($positions))) {
                $fail('As posições devem ser sequenciais a partir do 1º lugar.');
            }

            if (array_sum($value) !== 100) {
                $fail('A soma das porcentagens deve ser exatamente 100%.');
            }
        };
    }
}
