<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<x-partials.head :title="$title ?? null" />

<x-partials.body>
    <flux:header container class="border-b border-zinc-200 dark:border-zinc-700">
        <flux:brand :href="route('dashboard')" name="{{ config('app.name') }}" />
        <flux:navbar class="me-auto ms-4">
            <flux:navbar.item :href="route('dashboard')">Dashboard</flux:navbar.item>
        </flux:navbar>
        <flux:spacer />
        <flux:dropdown position="bottom" align="end">
            <flux:button variant="ghost">{{ auth()->user()->name }}</flux:button>
            <flux:menu>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <flux:menu.item as="button" type="submit">Sair</flux:menu.item>
                </form>
            </flux:menu>
        </flux:dropdown>
    </flux:header>
    <flux:main container>
        {{ $slot }}
    </flux:main>
</x-partials.body>

</html>
