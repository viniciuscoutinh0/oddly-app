@props(['pool'])

<a
    href="{{ route('pools.show', $pool) }}"
    {{ $attributes->merge(['class' => 'border transition duration-75 focus:outline-hidden hover:border-accent cursor-pointer flex flex-col sm:flex-row border-zinc-800 bg-zinc-900 rounded-xl overflow-hidden']) }}
>
    <div
        class="w-full sm:w-36 shrink-0 flex flex-col gap-3 items-center justify-center bg-zinc-950/40 p-4 border-b sm:border-b-0 sm:border-r border-zinc-800 text-center">
        <div
            class="bg-accent text-white rounded-md p-2 w-full font-bold text-[11px] uppercase tracking-wider line-clamp-3">
            {{ $pool->season->competition->name }}
        </div>
    </div>

    <div class="p-4 md:p-6 flex-1 min-w-0 flex flex-col justify-center">
        <flux:heading
            size="xl"
            title="{{ $pool->name }}"
        >
            {{ $pool->name }}
        </flux:heading>

        <div class="flex items-center gap-1.5 mt-1.5">
            <flux:icon.user-group
                variant="micro"
                class="text-zinc-400 shrink-0"
            />

            <flux:text
                class="text-xs"
                variant="subtle"
            >
                {{ $pool->participants_count }}
                {{ str('Participante')->plural($pool->participants_count) }}
            </flux:text>
        </div>
    </div>
</a>
