<div class="space-y-4">
    @if ($this->groupedTeams->isEmpty())
        <flux:callout icon="information-circle">
            A classificação dos grupos ainda não foi definida.
        </flux:callout>
    @else
        <div class="grid grid-cols-1 gap-4">
            @foreach ($this->groupedTeams as $letter => $teams)
                <div
                    class="overflow-hidden bg-zinc-900 border border-zinc-800 rounded-xl"
                    wire:key="group-{{ $letter }}"
                >
                    <div class="flex items-center gap-2 bg-zinc-800 px-3 py-1.5 border-b border-zinc-800">
                        <flux:text class="text-xs text-white">Grupo {{ $letter }}</flux:text>
                    </div>

                    <flux:table>
                        <flux:table.columns>
                            <flux:table.column
                                align="center"
                                class="w-8"
                            >#</flux:table.column>

                            <flux:table.column>Time</flux:table.column>

                            <flux:table.column
                                align="center"
                                class="hidden sm:table-cell w-10"
                            >
                                <flux:tooltip
                                    content="Jogos Disputados"
                                    position="top"
                                >J</flux:tooltip>
                            </flux:table.column>

                            <flux:table.column
                                align="center"
                                class="hidden sm:table-cell w-10"
                            >
                                <flux:tooltip
                                    content="Vitórias"
                                    position="top"
                                >C</flux:tooltip>
                            </flux:table.column>

                            <flux:table.column
                                align="center"
                                class="hidden sm:table-cell w-10"
                            >
                                <flux:tooltip
                                    content="Empates"
                                    position="top"
                                >E</flux:tooltip>
                            </flux:table.column>

                            <flux:table.column
                                align="center"
                                class="hidden sm:table-cell w-10"
                            >
                                <flux:tooltip
                                    content="Derrotas"
                                    position="top"
                                >D</flux:tooltip>
                            </flux:table.column>

                            <flux:table.column
                                align="center"
                                class="hidden sm:table-cell w-10"
                            >
                                <flux:tooltip
                                    content="Gols Pró"
                                    position="top"
                                >M</flux:tooltip>
                            </flux:table.column>

                            <flux:table.column
                                align="center"
                                class="hidden sm:table-cell w-10"
                            >
                                <flux:tooltip
                                    content="Gols Contra"
                                    position="top"
                                >S</flux:tooltip>
                            </flux:table.column>

                            <flux:table.column
                                align="center"
                                class="hidden sm:table-cell w-12"
                            >
                                <flux:tooltip
                                    content="Saldo de Gols"
                                    position="top"
                                >DG</flux:tooltip>
                            </flux:table.column>

                            <flux:table.column
                                align="center"
                                class="w-12"
                            >
                                <flux:tooltip
                                    content="Pontos"
                                    position="top"
                                >Pts</flux:tooltip>
                            </flux:table.column>
                        </flux:table.columns>

                        <flux:table.rows>
                            @foreach ($teams as $standing)
                                <flux:table.row :key="$standing->id">
                                    <flux:table.cell
                                        align="center"
                                        class="tabular-nums text-xs text-zinc-500 w-8"
                                    >{{ $standing->groupPosition ?: $loop->iteration }}°</flux:table.cell>

                                    <flux:table.cell variant="strong">
                                        <div class="flex items-center gap-2.5 min-w-0">
                                            @if (filled($standing->logoUrl))
                                                <img
                                                    src="{{ $standing->logoUrl }}"
                                                    alt="{{ $standing->name }}"
                                                    class="w-5 h-5 object-contain shrink-0"
                                                    loading="lazy"
                                                />
                                            @else
                                                <div class="w-5 h-5 rounded bg-zinc-800 border border-dashed border-zinc-700 shrink-0"></div>
                                            @endif

                                            <span class="truncate text-sm">{{ $standing->shortName }}</span>
                                        </div>
                                    </flux:table.cell>

                                    <flux:table.cell
                                        align="center"
                                        class="hidden sm:table-cell tabular-nums"
                                    >{{ $standing->matchesPlayed }}</flux:table.cell>

                                    <flux:table.cell
                                        align="center"
                                        class="hidden sm:table-cell tabular-nums"
                                    >{{ $standing->wins }}</flux:table.cell>

                                    <flux:table.cell
                                        align="center"
                                        class="hidden sm:table-cell tabular-nums"
                                    >{{ $standing->draws }}</flux:table.cell>

                                    <flux:table.cell
                                        align="center"
                                        class="hidden sm:table-cell tabular-nums"
                                    >{{ $standing->losses }}</flux:table.cell>

                                    <flux:table.cell
                                        align="center"
                                        class="hidden sm:table-cell tabular-nums"
                                    >{{ $standing->goalsFor }}</flux:table.cell>

                                    <flux:table.cell
                                        align="center"
                                        class="hidden sm:table-cell tabular-nums"
                                    >{{ $standing->goalsAgainst }}</flux:table.cell>

                                    <flux:table.cell
                                        align="center"
                                        class="hidden sm:table-cell tabular-nums"
                                    >
                                        <span class="{{ $standing->goalDifference > 0 ? 'text-green-400' : ($standing->goalDifference < 0 ? 'text-red-400' : '') }}">
                                            {{ $standing->goalDifference > 0 ? '+' : '' }}{{ $standing->goalDifference }}
                                        </span>
                                    </flux:table.cell>

                                    <flux:table.cell
                                        align="center"
                                        variant="strong"
                                        class="tabular-nums"
                                    >{{ $standing->points }}</flux:table.cell>
                                </flux:table.row>
                            @endforeach
                        </flux:table.rows>
                    </flux:table>
                </div>
            @endforeach
        </div>
    @endif
</div>
