<body class="antialiased relative bg-zinc-900/30 text-white text-base">
    {{ $slot }}

    @persist('toast')
        <flux:toast.group position="top center">
            <flux:toast />
        </flux:toast.group>
    @endpersist

    @fluxScripts
    @livewireScripts
    @vite('resources/js/app.js')
    @RegisterServiceWorkerScript
</body>
