<div class="space-y-8">
    <flux:callout
        variant="warning"
        icon="exclamation-circle"
        heading=" Pontuação é processada ao final do dia."
    />

    <div class="bg-zinc-900 border border-zinc-800 text-white rounded-xl mb-6 overflow-hidden">
        <div class="p-4 md:p-6 overflow-x-auto">
            <flux:table>
                <flux:table.columns>
                    <flux:table.column>#</flux:table.column>
                    <flux:table.column>Nome</flux:table.column>
                    <flux:table.column>Pontos</flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @foreach ($this->standings as $standing)
                        <flux:table.row :key="$standing->id">
                            <flux:table.cell>
                                {{-- blade-formatter-disable --}}
                            <flux:badge
                                :color="match($loop->iteration) {
                                    1 => 'amber',
                                    2 => 'zinc',
                                    3 => 'orange',
                                    default => null
                                }"
                                :icon="$loop->iteration <= 3 ? 'star' : null"
                                inset="top bottom"
                            >
                                {{ $loop->iteration  }}º
                            </flux:badge>
                                {{-- blade-formatter-enable --}}
                            </flux:table.cell>
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar
                                    size="xs"
                                    initials="{{ $standing->initials }}"
                                />
                                <flux:text variant="strong">{{ $standing->name }}</flux:text>
                            </flux:table.cell>
                            <flux:table.cell>{{ $standing->points }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
</div>
