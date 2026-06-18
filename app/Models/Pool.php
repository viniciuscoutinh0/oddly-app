<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Pool\Visibility;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class Pool extends Model
{
    /** @use HasFactory<\Database\Factories\PoolFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'visibility' => Visibility::class,
            'points_exact' => 'integer',
            'points_result' => 'integer',
            'points_champion' => 'integer',
            'points_group_position' => 'integer',
            'entry_fee' => 'integer',
        ];
    }

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function participants(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class, 'pool_user')
            ->withPivot('joined_at')
            ->withTimestamps();
    }

    public function distributions(): HasMany
    {
        return $this->hasMany(PoolPrizeDistribution::class);
    }

    public function totalAward(): int
    {
        return $this->entry_fee * $this->participants()->count();
    }

    public function isPublic(): bool
    {
        return $this->visibility === Visibility::Public;
    }

    public function isPrivate(): bool
    {
        return $this->visibility === Visibility::Private;
    }

    public function hasParticipant(User $user): bool
    {
        return $this->participants->contains($user->id);
    }

    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }
}
