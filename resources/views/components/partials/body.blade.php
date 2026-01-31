<body class="antialiased relative bg-slate-950 text-default text-base">
    {{ $slot }}

    @fluxScripts
    @livewireScripts
    @vite('resources/js/app.js')
</body>
