@props(['user', 'position' => 1])

@php
    $isFirst = $position === 1;
    $avatarColor = match ($position) {
        1 => 'amber',
        2 => 'slate',
        3 => 'orange',
        default => 'zinc',
    };
@endphp

<div
    {{ $attributes->class([
        'flex-1 relative flex flex-col items-center text-center bg-zinc-900 border border-zinc-800 border-t-2 rounded-xl px-3 pb-5',
        'order-2 pt-10 border-t-amber-400' => $position === 1,
        'order-1 pt-6  border-t-slate-400'  => $position === 2,
        'order-3 pt-4  border-t-orange-700' => $position === 3,
    ]) }}>

    {{-- Medal --}}
    <div class="absolute -top-3.5 inset-x-0 flex justify-center">
        <div @class([
            'flex items-center justify-center rounded-full text-white',
            'w-7 h-7 text-sm font-black bg-amber-400' => $position === 1,
            'w-6 h-6 text-xs font-bold bg-slate-400'  => $position === 2,
            'w-6 h-6 text-xs font-bold bg-orange-700' => $position === 3,
        ])>
            {{ $position }}
        </div>
    </div>

    <flux:avatar
        :initials="$user->initials"
        circle
        :size="$isFirst ? 'xl' : 'lg'"
        :color="$avatarColor"
    />

    <div class="mt-3 flex flex-col items-center gap-1.5">
        <flux:text class="w-full text-sm leading-snug text-balance">
            {{ $user->name }}
        </flux:text>

        <flux:heading
            :size="$isFirst ? 'xl' : 'lg'"
            class="tabular-nums"
        >
            {{ $user->points }}{{ str('pt')->plural($user->points) }}
        </flux:heading>

        <flux:text
            variant="subtle"
            class="text-xs tabular-nums"
        >
            {{ Number::currency($user->award) }}
        </flux:text>
    </div>
</div>
