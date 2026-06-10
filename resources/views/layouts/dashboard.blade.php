<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<x-partials.head :title="$title ?? null" />

<x-partials.body>
    <div class="flex h-screen w-full overflow-hidden">
        <x-sidebar />

        <div class="flex-1 flex flex-col min-w-0 h-full">
            <x-navbar />

            <flux:main
                container
                class="overflow-y-auto"
            >
                <div class="flex max-md:flex-col items-start">
                    <div class="flex-1 max-md:pt-6 self-stretch">
                        {{ $slot }}
                    </div>
                </div>
            </flux:main>
        </div>
    </div>
</x-partials.body>

</html>
