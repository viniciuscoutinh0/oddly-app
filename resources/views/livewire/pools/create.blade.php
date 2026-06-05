<div class="space-y-12">
    <div>
        <div class="mb-6">
            <flux:heading size="xl">
                Criar Bolão
            </flux:heading>

            <flux:text class="mt-1">
                Configure os detalhes da sua competição personalizada.
            </flux:text>
        </div>

        <form>
            <div class="grid grid-cols-2 gap-6">
                <div class="bg-zinc-900 border p-6 border-zinc-800 text-white rounded-sm">
                    <flux:fieldset>
                        <flux:legend class="mb-6">
                            Informações Básicas
                        </flux:legend>

                        <div class="space-y-6">
                            <flux:field>
                                <flux:label
                                    for="name"
                                    badge="Obrigátorio"
                                >Nome do bolão</flux:label>

                                <flux:input
                                    id="name"
                                    required
                                    placeholder="Exemplo: Bolão dos Firma 2026"
                                    autocomplete="off"
                                    wire:model="name"
                                />

                                <flux:error name="name" />
                            </flux:field>

                            <flux:field>
                                <flux:label
                                    for="visibilite"
                                    badge="Obrigátorio"
                                >
                                    Visibilidade
                                </flux:label>

                                <flux:radio.group
                                    variant="cards"
                                    class="max-sm:flex-col"
                                    wire:model="visibility"
                                >
                                    <flux:radio
                                        value="public"
                                        label="Público"
                                        icon="globe-alt"
                                        description="Qualquer usuário pode ver e participar"
                                    />

                                    <flux:radio
                                        value="privaty"
                                        label="Privado"
                                        icon="lock-closed"
                                        description="Disponível apenas via link ou convite"
                                    />
                                </flux:radio.group>
                            </flux:field>

                            <flux:field>
                                <flux:label badge="Obrigátorio">Temporada</flux:label>

                                <flux:select
                                    variant="listbox"
                                    placeholder="Selecione o campeonato..."
                                >
                                    @foreach ($seasons as $season)
                                        <flux:select.option :value="$season->id">{{ $season->name }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>

                                <flux:error name="email" />
                            </flux:field>
                        </div>
                    </flux:fieldset>
                </div>

                <div class="bg-zinc-900 border p-6 border-zinc-800 text-white rounded-sm">
                    <flux:fieldset>
                        <flux:legend class="mb-6">
                            Configuração de Pontuação
                        </flux:legend>

                        <div class="space-y-6">
                            <div
                                class="flex items-center border border-zinc-700 p-3 rounded-lg focus-within:ring-2 focus-within:ring-accent transition duration-75">
                                <div class="flex items-center gap-3 flex-1">
                                    <x-heroicon-m-flag class="w-5 h-5 shrink-0 text-zinc-400" />
                                    <flux:heading size="lg">
                                        Placar exato
                                    </flux:heading>
                                </div>
                                <flux:input
                                    class="max-w-32"
                                    type="number"
                                    wire:model="points_exact"
                                />
                            </div>
                            <div
                                class="flex items-center border border-zinc-700 p-3 rounded-lg focus-within:ring-2 focus-within:ring-accent transition duration-75">
                                <div class="flex items-center gap-3 flex-1">
                                    <x-heroicon-m-check-circle class="w-5 h-5 shrink-0 text-zinc-400" />
                                    <flux:heading size="lg">
                                        Resultado (V/E/D)
                                    </flux:heading>
                                </div>
                                <flux:input
                                    class="max-w-32"
                                    type="number"
                                    wire:model="points_result"
                                />
                            </div>
                            <div
                                class="flex items-center border border-zinc-700 p-3 rounded-lg focus-within:ring-2 focus-within:ring-accent transition duration-75">
                                <div class="flex items-center gap-3 flex-1">
                                    <x-heroicon-m-trophy class="w-5 h-5 shrink-0 text-zinc-400" />
                                    <flux:heading size="lg">
                                        Campeão Final
                                    </flux:heading>
                                </div>
                                <flux:input
                                    class="max-w-32"
                                    type="number"
                                    wire:model="points_champion"
                                />
                            </div>
                            <div
                                class="flex items-center border border-zinc-700 p-3 rounded-lg focus-within:ring-2 focus-within:ring-accent transition duration-75">
                                <div class="flex items-center gap-3 flex-1">
                                    <x-heroicon-m-rectangle-stack class="w-5 h-5 shrink-0 text-zinc-400" />
                                    <flux:heading size="lg">
                                        Classificado do Grupo
                                    </flux:heading>
                                </div>
                                <flux:input
                                    class="max-w-32"
                                    type="number"
                                    wire:model="points_group_position"
                                />
                            </div>

                            <flux:callout
                                icon="information-circle"
                                color="blue"
                            >


                                <flux:callout.heading>
                                    Dica
                                </flux:callout.heading>

                                <flux:callout.text>
                                    <p>Pontuações mais altas para resultados difíceis incentivam a competitividade.</p>
                                </flux:callout.text>
                            </flux:callout>

                        </div>
                    </flux:fieldset>
                </div>
            </div>
        </form>
    </div>

    {{-- <div class="max-w-xl mx-auto">
    <flux:heading size="xl" class="mb-6">Criar bolão</flux:heading>

    <form wire:submit="create" class="space-y-6">
        <flux:input label="Nome" required wire:model="name" />
        <flux:textarea label="Descrição" wire:model="description" />

        <flux:select label="Temporada" wire:model="season_id">
            <flux:select.option value="">Selecione…</flux:select.option>
            @foreach ($seasons as $season)
                <flux:select.option :value="$season->id">{{ $season->name }}</flux:select.option>
            @endforeach
        </flux:select>

        <flux:select label="Visibilidade" wire:model="visibility">
            <flux:select.option value="public">Público</flux:select.option>
            <flux:select.option value="private">Privado</flux:select.option>
        </flux:select>

        <flux:heading size="lg">Pontuação</flux:heading>
        <div class="grid grid-cols-2 gap-4">
            <flux:input type="number" label="Placar exato" wire:model="points_exact" />
            <flux:input type="number" label="Resultado" wire:model="points_result" />
            <flux:input type="number" label="Campeão" wire:model="points_champion" />
            <flux:input type="number" label="Posição no grupo" wire:model="points_group_position" />
        </div>

        <flux:button type="submit" variant="primary" color="cyan">Criar bolão</flux:button>
    </form>
</div> --}}
