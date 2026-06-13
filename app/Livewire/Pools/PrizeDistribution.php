<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Models\Pool;
use App\Models\PoolPrizeDistribution;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Locked;
use Livewire\Component;

final class PrizeDistribution extends Component
{
    #[Locked]
    public Pool $pool;

    public array $distributions = [];

    public function mount(): void
    {
        $this->distributions = $this->pool
            ->distributions
            ->map(fn (PoolPrizeDistribution $destribution) => [
                'position' => $destribution->position,
                'percentage' => $destribution->percentage,
            ])
            ->all() ?? [];
    }

    public function addPosition(): void
    {
        $this->distributions[] = [
            'position' => count($this->distributions) + 1,
            'percentage' => 0,
        ];
    }

    public function removePosition(int $index): void
    {
        unset($this->distributions[$index]);
        $this->distributions = array_values($this->distributions);

        foreach ($this->distributions as $i => &$distribution) {
            $distribution['position'] = $i + 1;
        }
    }

    #[Computed]
    public function totalPercentage(): int
    {
        return array_sum(array_column($this->distributions, 'percentage'));
    }

    public function render(): View|Factory
    {
        return view('livewire.pools.prize-distribution');
    }
}
