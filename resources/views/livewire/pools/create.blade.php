<div class="space-y-12">
    <div>
        <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
            <div>
                <flux:heading size="xl">
                    Criar Bolão
                </flux:heading>

                <flux:text class="mt-1">
                    Configure os detalhes da sua competição personalizada.
                </flux:text>
            </div>

            <div class="flex gap-3">
                <flux:button
                    variant="subtle"
                    :href="route('pools.index')"
                >
                    Voltar
                </flux:button>
                <flux:button
                    type="submit"
                    variant="primary"
                    form="create-pool"
                >
                    Salvar
                </flux:button>
            </div>
        </div>

        <form
            id="create-pool"
            wire:submit="create"
        >
            <div class="grid grid-cols-1 lg:grid-cols-2 items-start gap-6">
                <div class="bg-zinc-900 border p-6 border-zinc-800 text-white rounded-xl">
                    <flux:fieldset>
                        <flux:legend class="mb-6">
                            Informações Básicas
                        </flux:legend>

                        @if (filled($this->form->competition))
                            <div
                                class="p-3 border border-zinc-800 rounded-lg flex gap-3 items-center mb-6"
                                wire:transition
                            >
                                <div
                                    class="w-10 h-10 bg-zinc-800 text-xs font-black border border-zinc-700/50 flex items-center justify-center shrink-0 rounded-md">
                                    {{ $this->form->competition->code }}
                                </div>
                                <div class="flex-1">
                                    <flux:text class="mb-1 text-accent block text-xs">
                                        Competição Selecionada
                                    </flux:text>

                                    <flux:heading>
                                        {{ $this->form->competition->name }}
                                    </flux:heading>
                                </div>
                            </div>
                        @endif

                        <div class="space-y-6">
                            <flux:field>
                                <flux:label
                                    for="name"
                                    badge="Obrigátorio"
                                >
                                    Nome do bolão
                                </flux:label>

                                <flux:input
                                    id="name"
                                    required
                                    placeholder="Exemplo: Bolão dos Firma 2026"
                                    autocomplete="off"
                                    wire:model="form.name"
                                />
                                <flux:error name="form.name" />
                            </flux:field>

                            <flux:textarea
                                label="Descrição"
                                placeholder="Exemplo: Bolão dos amigos do trabalho. O último colocado paga o churrasco!"
                                wire:model="form.description"
                            />

                            <flux:field>
                                <flux:label badge="Obrigátorio">
                                    Visibilidade
                                </flux:label>

                                <flux:radio.group
                                    class="max-sm:flex-col"
                                    variant="cards"
                                    wire:model="form.visibility"
                                >
                                    @foreach ($this->visibilities as $visibility)
                                        <flux:radio
                                            :label="$visibility->getLabel()"
                                            :value="$visibility->value"
                                            :icon="$visibility->getIcon()"
                                            :description="$visibility->getDescription()"
                                        />
                                    @endforeach
                                </flux:radio.group>

                                <flux:error name="form.visibility" />
                            </flux:field>

                            <flux:field>
                                <flux:label badge="Obrigátorio">
                                    Competição
                                </flux:label>

                                <flux:select
                                    variant="listbox"
                                    placeholder="Selecione o campeonato..."
                                    wire:model.live="form.competition_id"
                                >
                                    @foreach ($this->competitions as $competition)
                                        <flux:select.option :value="$competition->id">
                                            {{ $competition->name }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>

                                <flux:error name="form.competition_id" />
                            </flux:field>

                            <flux:field>
                                <flux:label badge="Obrigátorio">
                                    Temporada
                                </flux:label>

                                <flux:select
                                    variant="listbox"
                                    placeholder="Selecione o campeonato..."
                                    wire:model="form.season_id"
                                >
                                    @foreach ($this->seasons as $season)
                                        <flux:select.option :value="$season->id">
                                            {{ $season->name }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>

                                <flux:error name="form.season_id" />
                            </flux:field>
                        </div>
                    </flux:fieldset>
                </div>

                <div class="bg-zinc-900 border p-6 border-zinc-800 text-white rounded-xl">
                    <flux:fieldset>
                        <flux:legend class="mb-6">
                            Configuração de Pontuação
                        </flux:legend>

                        <div class="space-y-6">
                            <div
                                class="flex items-center border border-zinc-700 p-3 rounded-lg focus-within:ring-2 focus-within:ring-accent focus-within:border-transparent transition duration-75">
                                <div class="flex items-center gap-3 flex-1">
                                    <x-heroicon-m-flag class="w-5 h-5 shrink-0 text-zinc-400" />
                                    <flux:heading size="lg">
                                        Placar exato
                                    </flux:heading>
                                </div>
                                <flux:input
                                    class="max-w-32"
                                    type="number"
                                    wire:model="form.points_exact"
                                />
                            </div>
                            <div
                                class="flex items-center border border-zinc-700 p-3 rounded-lg focus-within:ring-2 focus-within:ring-accent focus-within:border-transparent transition duration-75">
                                <div class="flex items-center gap-3 flex-1">
                                    <x-heroicon-m-check-circle class="w-5 h-5 shrink-0 text-zinc-400" />
                                    <flux:heading size="lg">
                                        Resultado (V/E/D)
                                    </flux:heading>
                                </div>
                                <flux:input
                                    class="max-w-32"
                                    type="number"
                                    wire:model="form.points_result"
                                />
                            </div>
                            <div
                                class="flex items-center border border-zinc-700 p-3 rounded-lg focus-within:ring-2 focus-within:ring-accent focus-within:border-transparent transition duration-75">
                                <div class="flex items-center gap-3 flex-1">
                                    <x-heroicon-m-trophy class="w-5 h-5 shrink-0 text-zinc-400" />
                                    <flux:heading size="lg">
                                        Campeão Final
                                    </flux:heading>
                                </div>
                                <flux:input
                                    class="max-w-32"
                                    type="number"
                                    wire:model="form.points_champion"
                                />
                            </div>
                            <div
                                class="flex items-center border border-zinc-700 p-3 rounded-lg focus-within:ring-2 focus-within:ring-accent focus-within:border-transparent transition duration-75">
                                <div class="flex items-center gap-3 flex-1">
                                    <x-heroicon-m-rectangle-stack class="w-5 h-5 shrink-0 text-zinc-400" />
                                    <flux:heading size="lg">
                                        Classificado do Grupo
                                    </flux:heading>
                                </div>
                                <flux:input
                                    class="max-w-32"
                                    type="number"
                                    wire:model="form.points_group_position"
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
</div>
