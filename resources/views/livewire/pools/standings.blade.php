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

    @if ($this->others->isNotEmpty())
        <div class="bg-zinc-900 border border-zinc-800 text-white rounded-xl mb-6 overflow-hidden">
            <div class="p-4 md:p-6 overflow-x-auto">
                <flux:table>
                    <flux:table.columns>
                        <flux:table.column>#</flux:table.column>
                        <flux:table.column>Nome</flux:table.column>
                        <flux:table.column>Pontos</flux:table.column>
                    </flux:table.columns>

                    <flux:table.rows>
                        @foreach ($this->others as $user)
                            <flux:table.row :key="$user->id">
                                <flux:table.cell>
                                    <flux:badge>
                                        {{ $loop->iteration + $this->leaders->count() }}º
                                    </flux:badge>
                                </flux:table.cell>
                                <flux:table.cell class="flex items-center gap-3">
                                    <flux:avatar
                                        size="xs"
                                        initials="{{ $user->initials }}"
                                    />
                                    <flux:text variant="strong">{{ $user->name }}</flux:text>
                                </flux:table.cell>
                                <flux:table.cell>{{ $user->points }}</flux:table.cell>
                            </flux:table.row>
                        @endforeach
                    </flux:table.rows>
                </flux:table>
            </div>
        </div>
    @endif
</div>
