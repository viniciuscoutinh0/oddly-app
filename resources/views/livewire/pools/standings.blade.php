<div class="space-y-8">
    <flux:callout
        variant="warning"
        icon="exclamation-circle"
        heading=" Pontuação é processada ao final do dia."
    />

    <div class="grid grid-cols-3 gap-3 sm:gap-3">
        @foreach ($this->leaders as $user)
            <x-standing.card
                :$user
                :position="$loop->iteration"
                wire:key="leader-{{ $user->id }}"
            />
        @endforeach

        @for ($i = $this->leaders->count(); $i < 3; $i++)
            <x-standing.card-ghost
                :position="$i"
                wire:key="ghost-{{ $i }}"
            />
        @endfor
    </div>

    <div>
        @foreach ($this->others as $user)
            <div
                class="flex items-stretch bg-zinc-900 mb-3 last:mb-0 border border-zinc-800 text-white rounded-xl overflow-hidden"
                wire:key="{{ $user->id }}"
            >
                <div class="w-14 sm:w-16 bg-zinc-800 flex items-center justify-center shrink-0">
                    <flux:heading size="xl">
                        {{ $loop->iteration + $this->leaders->count() }}º
                    </flux:heading>
                </div>

                <div class="flex flex-1 items-center gap-3 p-3 pr-4 sm:pr-6 min-w-0">
                    <flux:avatar
                        circle
                        size="sm"
                        initials="{{ $user->initials }}"
                        class="shrink-0"
                    />

                    <flux:text
                        variant="strong"
                        class="flex-1 min-w-0 truncate"
                    >
                        {{ $user->name }}
                    </flux:text>

                    <flux:text
                        variant="strong"
                        class="shrink-0 text-lg tabular-nums"
                    >
                        {{ $user->points }}pts
                    </flux:text>
                </div>
            </div>
        @endforeach
    </div>

</div>
