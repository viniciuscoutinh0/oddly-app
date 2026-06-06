<?php

declare(strict_types=1);

namespace App\Actions\Pool;

use App\Enums\Pool\Visibility;
use App\Models\Pool;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class CreatePoolAction
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function handle(User $owner, array $data): Pool
    {
        return DB::transaction(function () use ($owner, $data): Pool {
            $visibility = $data['visibility'];

            $pool = Pool::create([
                'name' => $data['name'],
                'slug' => $this->uniqueSlug($data['name']),
                'description' => $data['description'] ?? null,
                'season_id' => $data['season_id'],
                'owner_id' => $owner->id,
                'visibility' => $data['visibility'],
                'invite_code' => $visibility === Visibility::Private ? Str::upper(Str::random(8)) : null,
                'points_exact' => $data['points_exact'],
                'points_result' => $data['points_result'],
                'points_champion' => $data['points_champion'],
                'points_group_position' => $data['points_group_position'],
            ]);

            $pool->participants()->attach($owner->id, ['joined_at' => now()]);

            return $pool;
        });
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name);
        $slug = $base;

        while (Pool::where('slug', $slug)->exists()) {
            $slug = $base.'-'.Str::lower(Str::random(6));
        }

        return $slug;
    }
}
