<?php

use Livewire\Component;
use Livewire\Attributes\Validate;

new class extends Component {
    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required')]
    public string $password = '';

    #[Validate('boolean')]
    public bool $remember = false;

    public function login(): void
    {
        /** @var array<string, mixed> $data */
        $data = $this->validate();

        if (!auth()->attempt($this->credentials($data), (bool) $data['remember'])) {
            $this->addError('email', 'As credenciais fornecidas estão incorretas.');

            $this->reset('password');
            return;
        }

        session()->regenerate();

        $this->redirectRoute('dashboard');
    }

    private function credentials(array $data): array
    {
        return [
            'email' => $data['email'],
            'password' => $data['password'],
        ];
    }
};
?>

<div class="flex min-h-screen">
    <div class="flex items-center justify-center flex-1">
        <div class="max-w-80 w-80 mx-auto flex-1">
            <div class="text-center mb-6">
                <h1 class="text-2xl md:text-4xl font-extrabold text-secondary-500">{{ config('app.name') }}</h1>
            </div>

            <flux:heading
                class="text-center"
                size="xl"
            >Bora palpitar?</flux:heading>

            <form wire:submit="login">
                <div class="space-y-6">
                    <flux:input
                        label="E-mail"
                        placeholder="seu.email@exemplo.com"
                        required
                        wire:model="email"
                    />

                    <flux:input
                        label="Senha"
                        type="password"
                        placeholder="sua senha secreta"
                        required
                        viewable
                        wire:model="password"
                    />

                    <flux:field variant="inline">
                        <flux:checkbox wire:model="remember" />
                        <flux:label>Continuar logado</flux:label>
                        <flux:error name="remember" />
                    </flux:field>

                    <flux:button
                        type="submit"
                        variant="primary"
                        color="cyan"
                        class="min-w-full"
                    >Entrar no jogo</flux:button>

                    <flux:button
                        :href="route('register')"
                        variant="subtle"
                        class="min-w-full"
                    >Criar conta</flux:button>
                </div>
            </form>
        </div>
    </div>
    <div class="relative flex-1 overflow-hidden  p-0 max-lg:hidden">
        <img
            src="{{ asset('images/background.webp') }}"
            alt="{{ config('app.name') }}"
            class="object-cover w-full h-full"
            draggable="false"
            loading="lazy"
        />
    </div>
</div>
