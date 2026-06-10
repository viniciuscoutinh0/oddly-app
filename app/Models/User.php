<?php

declare(strict_types=1);

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\User\Role;
use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;

final class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'role' => Role::class,
        ];
    }

    public function name(): Attribute
    {
        return Attribute::get(fn ($value): string => (string) Str::title($value));
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->role === Role::Admin;
    }

    public function bets(): HasMany
    {
        return $this->hasMany(Bet::class);
    }

    public function ownedPools(): HasMany
    {
        return $this->hasMany(Pool::class, 'owner_id');
    }

    public function pools(): BelongsToMany
    {
        return $this
            ->belongsToMany(Pool::class, 'pool_user')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    public function championBets(): HasMany
    {
        return $this->hasMany(ChampionBet::class);
    }

    public function groupBets(): HasMany
    {
        return $this->hasMany(GroupBet::class);
    }

    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->map(fn (string $name): string => (string) Str::of($name)->substr(0, 1))
            ->take(2)
            ->implode('');
    }
}
