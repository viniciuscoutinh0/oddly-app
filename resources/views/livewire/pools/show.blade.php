<div class="space-y-8">
    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div class="flex items-start gap-3">
            <div class="shrink-0 w-16 h-16 md:w-20 md:h-20 rounded-xl overflow-hidden flex items-center justify-center">
                @if (filled($url = $pool->season->logo))
                    <img
                        src="{{ $url }}"
                        alt="Logo {{ $pool->season->competition->name }}"
                        loading="lazy"
                        class="w-full h-full object-cover"
                    />
                @else
                    <flux:icon.trophy
                        variant="solid"
                        class="w-8 h-8 md:w-12 md:h-12"
                    />
                @endif
            </div>

            <div class="min-w-0 flex flex-col gap-3">
                <div class="min-w-0">
                    <flux:heading size="xl">{{ $pool->name }}</flux:heading>

                    @if (filled($pool->description))
                        <flux:text
                            class="mt-1"
                            variant="subtle"
                        >{{ $pool->description }}</flux:text>
                    @endif
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <flux:badge
                        size="sm"
                        icon="trophy"
                        color="zinc"
                    >
                        {{ $pool->season->competition->name }}
                    </flux:badge>

                    <flux:badge
                        size="sm"
                        icon="calendar"
                        color="zinc"
                    >
                        Temporada {{ $pool->season->name }}
                    </flux:badge>

                    <flux:badge
                        size="sm"
                        :icon="$pool->visibility->getIcon()"
                        :color="$pool->isPublic() ? 'green' : 'zinc'"
                    >
                        {{ $pool->visibility->getLabel() }}
                    </flux:badge>

                    <flux:badge
                        size="sm"
                        icon="user-group"
                        color="zinc"
                    >
                        {{ $pool->participants_count }}
                        {{ str('participante')->plural($pool->participants_count) }}
                    </flux:badge>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <flux:button
                variant="subtle"
                icon="arrow-left"
                :href="route('pools.index')"
            >
                Voltar
            </flux:button>

            <flux:button
                icon="gift"
                x-on:click="$flux.modal('prize-distribution-manager').show()"
            >
                Definir Premiação
            </flux:button>

            <livewire:pools.leave :pool="$pool" />
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 items-start gap-6">
        <div class="lg:col-span-2">
            <flux:tab.group>
                <flux:tabs>
                    <flux:tab
                        name="standings"
                        icon="trophy"
                    >Ranking</flux:tab>
                    @can('bet', $pool)
                        <flux:tab
                            name="bets"
                            icon="document-check"
                        >Palpites</flux:tab>
                        <flux:tab
                            name="bonus"
                            icon="gift"
                        >Bônus</flux:tab>
                    @endcan
                </flux:tabs>

                <flux:tab.panel name="standings">
                    <livewire:pools.standings :$pool />
                </flux:tab.panel>

                @can('bet', $pool)
                    <flux:tab.panel name="bets">
                        <livewire:pools.bets :$pool />
                    </flux:tab.panel>

                    <flux:tab.panel name="bonus">
                        <livewire:pools.bonus :$pool />
                    </flux:tab.panel>
                @endcan
            </flux:tab.group>

            @cannot('bet', $pool)
                <flux:callout
                    class="mt-4"
                    icon="information-circle"
                >
                    Entre no bolão para fazer seus palpites e bônus.
                </flux:callout>
            @endcannot
        </div>

        <div class="lg:col-span-1 space-y-6">
            @can('seeInviteCode', $pool)
                <div class="bg-zinc-900 border border-zinc-800 text-white rounded-xl p-4 md:p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <flux:icon.ticket
                            variant="mini"
                            class="text-zinc-400 shrink-0"
                        />

                        <flux:heading size="lg">Convite</flux:heading>
                    </div>

                    <flux:text
                        variant="subtle"
                        class="text-xs mb-3 block"
                    >
                        Compartilhe o código para convidar participantes.
                    </flux:text>

                    <flux:input
                        icon="ticket"
                        :value="$pool->invite_code"
                        readonly
                        copyable
                    />
                </div>
            @endcan

            <div class="bg-zinc-900 border border-zinc-800 text-white rounded-xl p-4 md:p-6">
                <div class="flex items-center gap-3 mb-6">
                    <flux:icon.information-circle
                        variant="mini"
                        class="text-zinc-400 shrink-0"
                    />

                    <flux:heading size="lg">Informações</flux:heading>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center justify-between gap-3">
                        <flux:text
                            variant="subtle"
                            class="text-xs uppercase"
                        >
                            Organizador
                        </flux:text>

                        <div class="flex items-center gap-2 min-w-0">
                            <flux:avatar
                                size="xs"
                                :initials="$pool->owner->initials()"
                            />

                            <flux:text class="truncate">
                                {{ $pool->owner->name }}
                            </flux:text>
                        </div>
                    </div>

                    <flux:separator variant="subtle" />

                    <div class="flex items-center justify-between gap-3">
                        <flux:text
                            variant="subtle"
                            class="text-xs uppercase"
                        >
                            Período
                        </flux:text>

                        <flux:text class="text-end">
                            {{ $pool->season->start_date->format('d/m/Y') }}
                            –
                            {{ $pool->season->end_date->format('d/m/Y') }}
                        </flux:text>
                    </div>

                    <flux:separator variant="subtle" />

                    <div class="flex items-center justify-between gap-3">
                        <flux:text
                            variant="subtle"
                            class="text-xs uppercase"
                        >Bônus</flux:text>

                        @if (!$this->bonusLocksAt)
                            <flux:badge
                                size="sm"
                                color="zinc"
                            >Sem partidas</flux:badge>
                        @elseif ($this->bonusLocked)
                            <flux:badge
                                size="sm"
                                color="red"
                                icon="lock-closed"
                            >Encerrado</flux:badge>
                        @else
                            <flux:text class="text-end">
                                Encerra {{ $this->bonusLocksAt->format('d/m/Y H:i') }}
                            </flux:text>
                        @endif
                    </div>
                </div>
            </div>

            <div class="bg-zinc-900 border border-zinc-800 text-white rounded-xl p-4 md:p-6">
                <div class="flex items-center gap-3 mb-6">
                    <flux:icon.shield-check
                        variant="mini"
                        class="text-zinc-400 shrink-0"
                    />

                    <flux:heading size="lg">Regras de Pontuação</flux:heading>
                </div>

                <div class="grid grid-cols-1 gap-3">
                    @foreach ([['icon' => 'heroicon-m-flag', 'label' => 'Placar exato', 'points' => $pool->points_exact], ['icon' => 'heroicon-m-check-circle', 'label' => 'Resultado (V/E/D)', 'points' => $pool->points_result], ['icon' => 'heroicon-m-trophy', 'label' => 'Campeão final', 'points' => $pool->points_champion], ['icon' => 'heroicon-m-rectangle-stack', 'label' => 'Classificado do grupo', 'points' => $pool->points_group_position]] as $rule)
                        <div
                            class="bg-zinc-800/50 flex items-center justify-between gap-3 p-4 border-l-2 border-accent rounded-r-lg">
                            <div class="flex items-center gap-3 min-w-0">
                                <x-dynamic-component
                                    :component="$rule['icon']"
                                    class="w-5 h-5 shrink-0 text-zinc-400"
                                />
                                <flux:text class="text-xs uppercase">{{ $rule['label'] }}</flux:text>
                            </div>
                            <flux:heading
                                size="lg"
                                class="shrink-0"
                            >+{{ $rule['points'] }}</flux:heading>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <livewire:pools.prize-distribution :pool="$pool" />
</div>
