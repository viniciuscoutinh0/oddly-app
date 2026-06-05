<aside class="bg-zinc-900 flex flex-col border-r border-zinc-800 max-w-64 w-full px-4 py-6">
    <div class="flex items-center gap-3 mb-6">
        <div class="w-10 h-10 bg-accent text-white flex items-center justify-center rounded-sm shrink-0">
            <x-heroicon-s-trophy class="w-5 h-5 shrink-0" />
        </div>

        <div>
            <h1 class="text-accent text-xl -mb-0.5 font-black">Oddly</h1>
            <span class="text-zinc-400 font-medium text-xs">Facil, Simples!</span>
        </div>
    </div>

    <nav class="flex-1 space-y-2">
        <a
            href="#"
            class="flex items-center gap-3 w-full text-zinc-400 p-3 rounded-sm hover:text-sky-400 hover:bg-zinc-800 border-l-2 border-transparent hover:border-accent cursor-pointer focus:outline-hidden transition duration-75"
        >
            <x-heroicon-m-home class="w-4.5 h-4.5 shrink-0" />
            <span class="text-sm font-semibold">Dashboard</span>
        </a>
        <a
            href="#"
            class="flex items-center gap-3 w-full text-zinc-400 p-3 rounded-sm hover:text-sky-400 hover:bg-zinc-800 border-l-2 border-transparent hover:border-accent cursor-pointer focus:outline-hidden transition duration-75"
        >
            <x-heroicon-m-cog-6-tooth class="w-4.5 h-4.5 shrink-0" />
            <span class="text-sm font-semibold">Configurações</span>
        </a>
    </nav>

    <footer class="border-t flex flex-col gap-3 border-zinc-800 pt-4">
        <flux:button
            icon="plus"
            variant="primary"
            class="w-full"
            :href="route('pools.create')"
        >
            Criar Bolão
        </flux:button>

        <a
            href="#"
            class="flex items-center gap-3 w-full text-zinc-400 p-3 rounded-sm hover:text-sky-400 hover:bg-zinc-800 border-l-2 border-transparent hover:border-accent cursor-pointer focus:outline-hidden transition duration-75"
        >
            <x-heroicon-m-arrow-left-on-rectangle class="w-4.5 h-4.5 shrink-0" />
            <span class="text-sm font-semibold">Sair</span>
        </a>
    </footer>
</aside>
