<body class="antialiased relative bg-zinc-950 text-white text-base">
    {{ $slot }}

    @persist('toast')
        <flux:toast />
    @endpersist

    @fluxScripts
    @livewireScripts
    @vite('resources/js/app.js')
</body>
