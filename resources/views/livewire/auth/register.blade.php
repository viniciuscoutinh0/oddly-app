<div class="flex min-h-screen">
    <div class="flex items-center justify-center flex-1">
        <div class="max-w-80 w-80 mx-auto flex-1">
            <div class="text-center mb-6">
                <h1 class="text-2xl md:text-4xl font-extrabold text-secondary-500">{{ config('app.name') }}</h1>
            </div>

            <flux:heading class="text-center" size="xl">Criar conta</flux:heading>

            <form wire:submit="register">
                <div class="space-y-6">
                    <flux:input label="Nome" placeholder="Seu nome" required wire:model="name" />
                    <flux:input label="E-mail" placeholder="seu.email@exemplo.com" required wire:model="email" />
                    <flux:input label="Senha" type="password" viewable required wire:model="password" />
                    <flux:input label="Confirme a senha" type="password" viewable required wire:model="password_confirmation" />

                    <flux:button type="submit" variant="primary" color="cyan" class="min-w-full">Criar conta</flux:button>

                    <flux:text class="text-center">
                        Já tem conta?
                        <flux:link :href="route('login')">Entrar</flux:link>
                    </flux:text>
                </div>
            </form>
        </div>
    </div>
    <div class="flex-1 overflow-hidden rounded-lg p-16 max-lg:hidden">
        <img src="{{ asset('images/hero.webp') }}" alt="Oddly" class="object-cover w-full h-full rounded-lg" draggable="false" />
    </div>
</div>
