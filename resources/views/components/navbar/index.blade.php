<flux:header
    container
    class="relative bg-zinc-900 border-b border-zinc-800"
>
    <flux:sidebar.toggle
        class="lg:hidden"
        icon="bars-2"
    />

    <flux:brand
        href="#"
        :name="config('app.name')"
        class="max-lg:hidden"
    >
        <x-slot name="logo">
            <div class="size-6 rounded-md shrink-0 bg-accent text-accent-foreground flex items-center justify-center">
                <span class="font-bold">O</span>
            </div>
        </x-slot>
    </flux:brand>

    <flux:navbar class="-mb-px max-lg:hidden">
        <flux:navbar.item :href="route('dashboard')">
            Dashboard
        </flux:navbar.item>

        {{-- <flux:navbar.item :href="route('pools.index')">
            Bolões
        </flux:navbar.item> --}}
    </flux:navbar>

    <flux:spacer />

    <flux:dropdown
        position="bottom"
        align="end"
    >
        <flux:profile :initials="auth()->user()->initials()" />

        <flux:menu>
            {{-- <flux:menu.group heading="Conta">
                <flux:menu.item>Perfil</flux:menu.item>
            </flux:menu.group> --}}

            <flux:menu.item :href="route('logout')">Sair</flux:menu.item>
        </flux:menu>
    </flux:dropdown>
</flux:header>
