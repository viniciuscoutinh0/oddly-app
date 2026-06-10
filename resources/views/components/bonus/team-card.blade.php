@props(['team', 'selected' => false, 'disabled' => false])

<button
    type="button"
    @disabled($disabled)
    {{ $attributes->class([
        'group relative flex flex-col items-center gap-3 p-4 border rounded-xl bg-zinc-900 transition duration-75 disabled:opacity-50 disabled:pointer-events-none',
        'border-accent ring-1 ring-accent' => $selected,
        'border-zinc-800 hover:border-zinc-600' => ! $selected,
    ]) }}
>
    @if ($selected)
        <span class="absolute top-2 right-2 flex items-center justify-center w-5 h-5 rounded-full bg-accent text-accent-foreground">
            <x-heroicon-m-check class="w-3.5 h-3.5" />
        </span>
    @endif

    <div class="relative w-12 h-12">
        @if (filled($team->logo_url))
            <img
                src="{{ $team->logo_url }}"
                alt="{{ $team->name }}"
                class="w-full h-full object-contain"
            />
        @else
            <div class="w-full h-full rounded-md bg-zinc-800/15 border border-dashed border-zinc-800"></div>
        @endif
    </div>

    <flux:text class="text-center text-sm text-white wrap-break-word">
        {{ $team->name }}
    </flux:text>
</button>
