<div class="space-y-6">
    <flux:callout
        variant="warning"
        icon="exclamation-circle"
        heading=" Pontuação é processada ao final do dia."
    />

    <div class="bg-zinc-900 border border-zinc-800 text-white rounded-xl mb-6 overflow-hidden">
        <div class="p-6">
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
                                    2 => 'gray',
                                    3 => 'yellow'
                                }"
                                :icon:trailing="$loop->iteration <= 3 ? 'star' : null"
                                rounded

                                inset="top bottom"
                            >
                                {{ $loop->iteration  }}
                            </flux:badge>
                        {{-- blade-formatter-enable --}}
                            </flux:table.cell>
                            <flux:table.cell class="flex items-center gap-3">
                                <flux:avatar
                                    size="xs"
                                    initials="{{ $standing->initials }}"
                                />
                                {{ $standing->name }}
                            </flux:table.cell>
                            <flux:table.cell>{{ $standing->points }}</flux:table.cell>
                        </flux:table.row>
                    @endforeach
                </flux:table.rows>
            </flux:table>
        </div>
    </div>
</div>
