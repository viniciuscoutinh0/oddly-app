<head>
    <meta charset="utf-8">
    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>{{ $title ?? config('app.name') }}</title>

    <link
        rel="stylesheet"
        href="https://rsms.me/inter/inter.css"
    />

    @fluxAppearance
    @livewireStyles
    @vite('resources/css/app.css')
</head>
