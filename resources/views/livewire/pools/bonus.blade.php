<div class="space-y-8">
    @if ($this->locked)
        <flux:callout variant="warning">Os palpites bônus estão encerrados.</flux:callout>
    @endif

    <div class="overflow-hidden bg-zinc-900 border border-zinc-800 rounded-xl">
        <div class="flex items-center justify-between gap-2 bg-zinc-800 text-white px-3 py-1.5 border-b border-zinc-800">
            <flux:text class="text-xs text-white">
                Campeão
            </flux:text>

            <flux:badge
                size="sm"
                :color="$championTeamId ? 'green' : 'zinc'"
            >
                {{ $championTeamId ? 1 : 0 }}/1
            </flux:badge>
        </div>

        <div class="p-3">
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                @foreach ($this->allTeams as $team)
                    <x-bonus.team-card
                        :team="$team"
                        :selected="$championTeamId === $team->id"
                        :disabled="$this->locked"
                        wire:click="$set('championTeamId', {{ $team->id }})"
                        wire:key="champion-{{ $team->id }}"
                    />
                @endforeach
            </div>

            <flux:error name="championTeamId" />
        </div>
    </div>

    <div>
        <flux:heading
            size="lg"
            class="mb-3"
        >Classificados por grupo</flux:heading>
        <flux:text
            variant="subtle"
            class="mb-4 block text-sm"
        >
            Selecione até 2 times que avançam em cada grupo.
        </flux:text>

        @foreach ($this->groupLetters as $letter)
            @php
                $selected = $groups[$letter] ?? [];
                $selectedCount = count($selected);
            @endphp

            <div
                class="overflow-hidden bg-zinc-900 mb-3 last:mb-0 border border-zinc-800 rounded-xl"
                wire:key="group-{{ $letter }}"
            >
                <div
                    class="flex items-center justify-between gap-2 bg-zinc-800 text-white px-3 py-1.5 border-b border-zinc-800">
                    <flux:text class="text-xs text-white">
                        Grupo {{ $letter }}
                    </flux:text>

                    <flux:badge
                        size="sm"
                        :color="$selectedCount === 2 ? 'green' : 'zinc'"
                    >
                        {{ $selectedCount }}/2
                    </flux:badge>
                </div>

                <div class="p-3">
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
                        @foreach ($this->teamsInGroup($letter) as $team)
                            <x-bonus.team-card
                                :team="$team"
                                :selected="in_array($team->id, $selected, true)"
                                :disabled="$this->locked ||
                                    ($selectedCount >= 3 && !in_array($team->id, $selected, true))"
                                wire:click="toggleGroup('{{ $letter }}', {{ $team->id }})"
                                wire:key="group-{{ $letter }}-team-{{ $team->id }}"
                            />
                        @endforeach
                    </div>

                    <flux:error name="groups.{{ $letter }}" />
                </div>
            </div>
        @endforeach
    </div>
</div>
