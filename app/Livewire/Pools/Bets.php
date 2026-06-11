<?php

declare(strict_types=1);

namespace App\Livewire\Pools;

use App\Actions\Bet\PlaceBetAction;
use App\Exceptions\Bet\BetException;
use App\Models\Bet;
use App\Models\Fixture;
use App\Models\Pool;
use App\Models\User;
use Exception;
use Flux\Flux;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Lazy;
use Livewire\Attributes\Locked;
use Livewire\Component;

/**
 * @property User $user
 * @property Collection<Fixture> $fixtures
 * @property Collection<Fixture> $groups
 */
#[Lazy]
final class Bets extends Component
{
    #[Locked]
    public Pool $pool;

    /**
     * @var array<int, array{home: int|null, away: int|null}>
     */
    public array $scores = [];

    #[Computed]
    public function user(): User
    {
        return Auth::user();
    }

    public function mount(): void
    {
        abort_unless(Gate::allows('bet', $this->pool), code: 403);

        $bets = Bet::query()
            ->where('user_id', $this->user->id)
            ->whereIn('fixture_id', $this->fixtures->pluck('id'))
            ->get()
            ->keyBy('fixture_id');

        foreach ($this->fixtures as $fixture) {
            $this->scores[$fixture->id] = [
                'home' => $bets[$fixture->id]->home_score ?? null,
                'away' => $bets[$fixture->id]->away_score ?? null,
            ];
        }
    }

    public function save(int $id, int $homeScore, int $awayScore): void
    {
        /** @var ?Fixture $fixture */
        $fixture = $this->fixtures->firstWhere('id', $id);

        if (! $fixture) {
            return;
        }

        try {
            app(PlaceBetAction::class)->handle(
                $this->user,
                $fixture,
                $homeScore,
                $awayScore,
            );

            Flux::toast(
                heading: 'Palpite salvo! 🎯',
                text: 'Seu palpite foi registrado com sucesso.',
                variant: 'success',
            );
        } catch (BetException $exception) {
            Flux::toast(
                heading: 'Impedimento! 🚩',
                text: $exception->getMessage(),
                variant: 'danger',
            );
        }
    }

    /**
     * @return Collection<int, Fixture>
     */
    #[Computed]
    public function fixtures(): Collection
    {
        return $this->pool
            ->season
            ->fixtures()
            ->with([
                'homeTeam',
                'awayTeam',
                'stage',
            ])
            ->get()
            ->sortBy(fn (Fixture $fixture): string => sprintf(
                '%03d-%s',
                $fixture->stage->sort_order,
                $fixture->match_date->format('YmdHis'),
            ))
            ->values();
    }

    #[Computed]
    public function groups(): Collection
    {
        return $this->fixtures
            ->groupBy(fn (Fixture $fixture): string => $fixture->match_day
                ? "Fase de Grupos - Rodada {$fixture->match_day}"
                : $fixture->stage->name->getLabel());
    }

    #[Computed]
    public function bets(): Collection
    {
        return collect($this->scores)
            ->filter(fn (array $values): bool => collect($values)->some(fn (?int $value): bool => filled($value)));
    }

    public function placeholder(): View
    {
        return view('components.bet.placeholder');
    }

    public function render(): View
    {
        return view('livewire.pools.bets');
    }
}
