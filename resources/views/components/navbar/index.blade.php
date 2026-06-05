<div class="border-b border-zinc-800 h-16 w-full">
    <div class="flex items-center justify-between w-full max-w-7xl mx-auto h-full sm:px-10 xl:px-0">
        <nav class="flex gap-6 h-full">
            <a
                href="{{ route('dashboard') }}"
                class="text-accent border-b-2 border-accent -mb-px h-full flex items-center text-sm cursor-pointer focus:outline-hidden transition duration-75"
            >
                Dashboard
            </a>

            <a
                href="{{ route('pools.index') }}"
                class="text-zinc-400 border-b-2 border-transparent hover:text-accent hover:border-accent -mb-px h-full flex items-center text-sm cursor-pointer focus:outline-hidden transition duration-75"
            >
                Bolões
            </a>
            <a
                href="#"
                class="text-zinc-400 border-b-2 border-transparent hover:text-zinc-200 -mb-px h-full flex items-center text-sm cursor-pointer focus:outline-hidden transition duration-75"
            >
                Ranking
            </a>
            <a
                href="#"
                class="text-zinc-400 border-b-2 border-transparent hover:text-zinc-200 -mb-px h-full flex items-center text-sm cursor-pointer focus:outline-hidden transition duration-75"
            >
                Resultados
            </a>
        </nav>

        <div class="shrink-0 flex items-center gap-3">
            <span class="text-xs font-medium text-zinc-400">Olá, Vinicius Coutinho</span>

            <flux:avatar
                name="Vinicius Coutinho"
                size="sm"
            />
        </div>
    </div>
</div>
