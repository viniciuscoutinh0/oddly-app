@props(['team', 'model', 'disabled' => false, 'placeholder' => 'A definir'])

<div class="flex flex-col items-center gap-3">
    <div class="relative w-12 h-12">
        @if (filled($team))
            <img
                src="{{ $team->logo_url }}"
                alt="{{ $team->name }}"
                class="w-full h-full object-contain"
            />
        @else
            <div class="w-full h-full rounded-md bg-zinc-800/15 border border-dashed border-zinc-800"></div>
        @endif
    </div>

    <flux:heading
        size="lg"
        class="text-center text-sm sm:text-lg wrap-break-word"
    >
        {{ $team?->name ?? $placeholder }}
    </flux:heading>

    <flux:button.group>
        <flux:button
            icon="minus"
            size="sm"
            variant="filled"
            x-on:click="expired ? false : {{ $model }} = Math.max(0, (parseInt({{ $model }}) || 0) - 1)"
            :disabled="$disabled || blank($team)"
        />
        <flux:button
            icon="plus"
            size="sm"
            variant="filled"
            x-on:click="expired ? false : {{ $model }} = (parseInt({{ $model }}) || 0) + 1"
            :disabled="$disabled || blank($team)"
        />
    </flux:button.group>
</div>
