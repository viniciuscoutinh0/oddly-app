@props(['pool'])

<a
    href="{{ route('pools.show', $pool) }}"
    {{ $attributes->merge(['class' => 'border transition duration-75 focus:outline-hidden hover:border-accent cursor-pointer flex flex-col sm:flex-row border-zinc-800 bg-zinc-900 rounded-xl overflow-hidden']) }}
>

    <div
        class="relative w-full sm:w-36 shrink-0 flex flex-col gap-3 items-center justify-center bg-accent p-4 border-b sm:border-b-0 sm:border-r border-zinc-800 text-center"
        @if ($pool->season->emblem_url) style="background-image: url('{{ $pool->season->emblem_url }}'); background-size: cover; background-position: center; background-repeat: no-repeat;" @endif
    >
    </div>

    <div class="p-4 md:p-6 flex-1 min-w-0 flex flex-col justify-center">
        <div class="flex items-center justify-between">
            <flux:heading
                size="xl"
                title="{{ $pool->name }}"
            >
                {{ $pool->name }}
            </flux:heading>

            <flux:badge
                icon="trophy"
                size="sm"
            >
                {{ $pool->season->competition->name }}
            </flux:badge>
        </div>

        <div class="flex items-center gap-1.5 mt-1.5 mb-2 md:mb-3">
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

        <flux:field>
            <flux:label>
                Completo
                <x-slot name="trailing">
                    <span class="tabular-nums">{{ $pool->season->progress() }}%</span>
                </x-slot>
            </flux:label>
            <flux:progress :value="$pool->season->progress()" />
        </flux:field>
    </div>
</a>
