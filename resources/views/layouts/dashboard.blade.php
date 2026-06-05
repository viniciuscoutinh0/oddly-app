<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<x-partials.head :title="$title ?? null" />

<x-partials.body>
    <div class="flex h-screen w-full overflow-hidden">
        <x-sidebar />

        <div class="flex-1 flex flex-col min-w-0 h-full">
            <x-navbar />

            <main class="flex-1 overflow-y-auto p-10">
                <div class="max-w-7xl mx-auto w-full space-y-6">
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>
</x-partials.body>

</html>
