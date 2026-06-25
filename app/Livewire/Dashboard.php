<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\Fixture;
use App\Models\Pool;
use App\Models\User;
use App\Services\User\UserDashboard;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * @property User $user
 * @property Collection<Pool> $pools
 * @property Collection<array{fixture: Fixture, pool: Pool}> $urgentFixtures
 * @property Collection<Fixture> $upcomingFixtures
 */
#[Layout('layouts.dashboard')]
final class Dashboard extends Component
{
    #[Computed]
    public function user(): User
    {
        return Auth::user() ?? throw new \RuntimeException('Unauthenticated.');
    }

    #[Computed]
    public function pools(): Collection
    {
        return app(UserDashboard::class)->poolsForUser($this->user);
    }

    #[Computed]
    public function urgentFixtures(): Collection
    {
        return app(UserDashboard::class)->urgentFixtures($this->user, $this->pools);
    }

    #[Computed]
    public function upcomingFixtures(): Collection
    {
        return app(UserDashboard::class)->upcomingFixtures($this->pools);
    }

    public function render(): View
    {
        return view('livewire.dashboard');
    }
}
