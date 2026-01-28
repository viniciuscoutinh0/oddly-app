<x-layouts.app>
    <header class="bg-slate-900/75 backdrop-blur-lg h-16 border-b border-slate-800 sticky top-0 inset-x-0 z-50">
        <div class="max-w-3xl mx-auto flex items-center h-full px-4">
            <h1 class="text-xl text-secondary-500 font-extrabold mr-4 shrink-0">Oddly</h1>
            <nav class="flex gap-4 h-full justify-items-center">
                <a href="#" aria-current="page"
                    class="flex items-center h-full border-b-2 border-transparent text-muted hover:text-accent hover:border-primary-400 text-sm font-medium cursor-pointer transition duration-75 px-2 shrink-0 aria-[current]:text-accent aria-[current]:border-primary-400 outline-0">Início</a>
                <a href="#"
                    class="flex items-center h-full border-b-2 border-transparent text-muted hover:text-accent hover:border-primary-400 text-sm font-medium cursor-pointer transition duration-75 px-2 shrink-0 aria-[current]:text-accent aria-[current]:border-primary-400 outline-0">O
                    que é Oddly?</a>
                <a href="#"
                    class="flex items-center h-full border-b-2 border-transparent text-muted hover:text-accent hover:border-primary-400 text-sm font-medium cursor-pointer transition duration-75 px-2 shrink-0 aria-[current]:text-accent aria-[current]:border-primary-400 outline-0">Como
                    funciona</a>
                <a href="#"
                    class="flex items-center h-full border-b-2 border-transparent text-muted hover:text-accent hover:border-primary-400 text-sm font-medium cursor-pointer transition duration-75 px-2 shrink-0 aria-[current]:text-accent aria-[current]:border-primary-400 outline-0">Campeonatos</a>
                <a href=""
                    class="flex items-center h-full border-b-2 border-transparent text-muted hover:text-accent hover:border-primary-400 text-sm font-medium cursor-pointer transition duration-75 px-2 shrink-0 aria-[current]:text-accent aria-[current]:border-primary-400 outline-0">Entrar
                    ou Criar minha conta
                </a>
            </nav>
        </div>
    </header>

    <div class="max-w-3xl w-full px-4 mx-auto">
        <section class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 my-8 md:my-12">
            <img src="{{ asset('images/hero.png') }}" class="w-full h-[220px] sm:h-[280px] md:h-full object-cover"
                alt="Hero image">
            <div>
                <h1 class="text-3xl md:text-4xl font-semibold mb-6 md:mb-8">
                    Jogue com seus amigos, <span class="text-secondary-500">palpite em cada partida e crie seu
                        bolão</span>
                </h1>
                <a href=""
                    class="h-10 md:h-8 bg-primary-400 font-medium rounded-md text-slate-950 cursor-pointer transition duration-75 hover:bg-primary-300 hover:scale-105 flex items-center justify-center w-full">
                    <x-heroicon-m-trophy class="w-5 h-5 mr-2" />
                    Começar
                    agora</a>
            </div>
        </section>

        <section class="mb-12 md:mb-16">
            <h2 class="text-2xl md:text-3xl font-semibold">
                O que é <span class="font-semibold text-secondary-500">Oddly</span>?
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 md:gap-8 mb-6 md:mb-8">
                <div class="flex flex-col gap-4">
                    <p class="text-sm/6 md:text-base/7 text-subtle"><span
                            class="font-medium text-secondary-500">Oddly</span> é a
                        plataforma onde palpitar vira jogo. Aqui você cria bolões
                        com seus amigos, dá seus
                        palpites nos principais campeonatos do mundo e disputa posição no ranking a cada rodada.
                        Mas não é só acertar placar.</p>
                    <p class="text-sm/6 md:text-base/7 text-subtle">No <span
                            class="font-medium text-secondary-500">Oddly</span>,
                        cada
                        palpite conta pontos, gera conquistas e desbloqueia
                        badges que mostram o seu
                        estilo de jogo: o profeta, o consistente, o invicto ou aquele que passou longe 😅
                        Quanto mais você participa, mais evolui.</p>
                    <p class="text-sm/6 md:text-base/7 text-subtle">
                        Quanto mais acerta, mais destaque você ganha.
                        <span class="font-medium text-secondary-500">Oddly</span> é competição, zoeira, estratégia e
                        diversão — tudo em um só lugar.
                    </p>
                </div>
                <img src="{{ asset('images/badges.png') }}" class="object-cover w-full mx-auto">
            </div>

        </section>

        <section class="mb-12 md:mb-16">
            <h3 class="text-accent text-xl md:text-2xl font-semibold tracking-wide mb-6 md:mb-8">Como funciona?</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                <div class="bg-slate-900 rounded-md border border-slate-800 p-4 md:p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 shrink-0 bg-secondary-500 rounded-md flex items-center justify-center">
                            <x-heroicon-m-user-plus class="w-5 h-5 text-accent shrink-0" />
                        </div>
                        <h4 class="font-medium text-accent text-sm md:text-base">Cadastre-se</h4>
                    </div>
                    <p class="text-xs/6 md:text-sm/6 text-subtle">Entre rápido e sem complicação.
                        Crie sua conta e esteja pronto para palpitar em minutos.</p>
                </div>
                <div class="bg-slate-900 rounded-md border border-slate-800 p-4 md:p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 shrink-0 bg-secondary-500 rounded-md flex items-center justify-center">
                            <x-heroicon-m-squares-2x2 class="w-5 h-5 text-accent shrink-0" />
                        </div>
                        <h4 class="font-medium text-accent text-sm md:text-base">Escolha o campeonato</h4>
                    </div>
                    <p class="text-xs/6 md:text-sm/6 text-subtle">Champions League, Copa do Mundo, Brasileirão e muito
                        mais.
                        Você escolhe onde quer mostrar seu talento.</p>
                </div>
                <div class="bg-slate-900 rounded-md border border-slate-800 p-4 md:p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 shrink-0 bg-secondary-500 rounded-md flex items-center justify-center">
                            <x-heroicon-m-plus-circle class="w-5 h-5 text-accent shrink-0" />
                        </div>
                        <h4 class="font-medium text-accent text-sm md:text-base">Crie seu bolão</h4>
                    </div>
                    <p class="text-xs/6 md:text-sm/6 text-subtle">Convide amigos, familiares ou colegas de trabalho.
                        Defina regras, pontuação e comece a disputa.</p>
                </div>
                <div class="bg-slate-900 rounded-md border border-slate-800 p-4 md:p-6">
                    <div class="flex items-center gap-3 mb-3">
                        <div class="w-10 h-10 shrink-0 bg-secondary-500 rounded-md flex items-center justify-center">
                            <x-heroicon-m-puzzle-piece class="w-5 h-5 text-accent shrink-0" />
                        </div>
                        <h4 class="font-medium text-accent text-sm md:text-base">Faça seu palpite</h4>
                    </div>
                    <p class="text-xs/6 md:text-sm/6 text-subtle">Dê seus palpites jogo a jogo, acompanhe os resultados
                        em tempo real
                        e veja quem está mandando melhor.</p>
                </div>
            </div>
        </section>
    </div>
    <div class="bg-slate-900 py-12 md:py-16">
        <div class="max-w-3xl mx-auto w-full px-4">
            <section>
                <header class="mb-6 md:mb-8">
                    <h4 class="text-accent text-xl md:text-2xl font-semibold tracking-wide mb-2">Campeonatos</h4>
                    <p class="text-subtle text-sm md:text-base">Mostre sua habilidade nos palpites, acompanhe os jogos e
                        dispute o
                        topo do ranking em tempo real.
                    </p>
                </header>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 md:gap-8">
                    <div class="flex flex-col items-center bg-slate-800 rounded-lg p-6 md:p-4 gap-3">
                        <div class="w-18 h-18 rounded-lg bg-white"></div>
                        <h4 class="text-sm md:text-base font-semibold text-accent">UEFA Champions League</h4>
                        <p class="text-subtle text-xs md:text-sm text-center md:text-justify">Palpite nos maiores
                            confrontos da
                            Europa e dispute rodada a
                            rodada com seus amigos para ver quem realmente entende de futebol.</p>
                    </div>
                    <div class="flex flex-col items-center bg-slate-800 rounded-lg p-6 md:p-4 gap-3">
                        <div class="w-18 h-18 rounded-lg bg-white"></div>
                        <h4 class="text-sm md:text-base font-semibold text-accent">FIFA World Cup 2026</h4>
                        <p class="text-subtle text-xs md:text-sm text-center md:text-justify">A Copa do Mundo é o palco
                            perfeito
                            para provar seus palpites.
                            Crie bolões, acumule pontos e conquiste badges a cada fase.</p>
                    </div>
                    <div class="flex flex-col items-center bg-slate-800 rounded-lg p-6 md:p-4 gap-3">
                        <div class="w-18 h-18 rounded-lg bg-white"></div>
                        <h4 class="text-sm md:text-base font-semibold text-accent">Brasileirão Betano</h4>
                        <p class="text-subtle text-xs md:text-sm text-center md:text-justify">Todos os jogos, todas as
                            rodadas.
                            Palpite no campeonato mais disputado do Brasil e veja quem é o mais regular até o fim.</p>
                    </div>
                </div>
            </section>
        </div>
    </div>

    <div class="max-w-3xl w-full px-4 py-12 md:py-16 mx-auto">
        <section>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 md:gap-12 items-center">
                <div>
                    <h2 class="text-2xl md:text-4xl font-semibold mb-4 md:mb-6">Pronto para provar que você entende
                        de futebol?</h2>
                    <p class="text-subtle text-sm/6 md:text-base/7 mb-6">Crie seu bolão em poucos minutos, chame seus
                        amigos e dispute cada rodada como se fosse final
                        de campeonato.</p>

                    <ul class="space-y-3">
                        <li class="flex items-center text-sm md:text-base text-accent">
                            <x-heroicon-m-check-circle class="w-5 h-5 text-secondary-500 mr-3 shrink-0" />
                            Rankings em tempo real
                        </li>
                        <li class="flex items-center text-sm md:text-base text-accent">
                            <x-heroicon-m-check-circle class="w-5 h-5 text-secondary-500 mr-3 shrink-0" />
                            Badges e conquistas exclusivas
                        </li>
                        <li class="flex items-center text-sm md:text-base text-accent">
                            <x-heroicon-m-check-circle class="w-5 h-5 text-secondary-500 mr-3 shrink-0" />
                            Todos os principais campeonatos
                        </li>
                        <li class="flex items-center text-sm md:text-base text-accent">
                            <x-heroicon-m-check-circle class="w-5 h-5 text-secondary-500 mr-3 shrink-0" />
                            Grátis e sem complicação
                        </li>
                    </ul>
                </div>

                <div class="bg-slate-900 rounded-lg border border-slate-800 p-6 md:p-8">
                    <div class="mb-6">
                        <h3 class="font-semibold text-xl md:text-2xl text-accent mb-2">Entre no jogo agora</h3>
                        <p class="text-xs/6 md:text-sm/7 text-subtle">É rápido, grátis e sem complicação.</p>
                    </div>
                    <a href=""
                        class="h-12 md:h-10 bg-primary-400 font-medium text-sm md:text-base rounded-lg text-slate-950 cursor-pointer transition duration-75 hover:bg-primary-300 hover:scale-105 flex items-center justify-center w-full mb-3">
                        Criar meu bolão agora
                    </a>
                    <p class="text-xs text-center text-muted">Leva menos de 1 minuto. Sem cartão de crédito.</p>
                </div>
            </div>
        </section>
    </div>
    <footer class="bg-slate-900 border-t border-slate-800 py-6 mt-12">
        <div class="max-w-3xl w-full px-4 md:py-16 mx-auto">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <h5 class="text-sm font-semibold tracking-wide text-secondary-500 mb-2">Oddly</h5>
                    <p class="text-xs text-muted">O jogo onde seus palpites viram disputa. Crie bolões, chame amigos e
                        prove que você manja de futebol.</p>
                </div>
                <div>
                    <h5 class="text-sm font-semibold tracking-wide text-accent mb-2">Produto</h5>
                    <nav class="flex flex-col gap-2">
                        <a href="#"
                            class="text-muted text-sm transition duration-75 hover:text-accent hover:underline">Oque é
                            Oddly?</a>
                        <a href="#"
                            class="text-muted text-sm transition duration-75 hover:text-accent hover:underline">Como
                            funciona?</a>
                        <a href="#"
                            class="text-muted text-sm transition duration-75 hover:text-accent hover:underline">Campeonatos?</a>
                        <a href="#"
                            class="text-muted text-sm transition duration-75 hover:text-accent hover:underline">Criar
                            Bolão?</a>
                    </nav>
                </div>
                <div>
                    <h5 class="text-sm font-semibold tracking-wide text-accent mb-2">Ajuda</h5>
                    <nav class="flex flex-col gap-2">
                        <a href="#"
                            class="text-muted text-sm transition duration-75 hover:text-accent hover:underline">Central
                            de ajuda</a>
                        <a href="#"
                            class="text-muted text-sm transition duration-75 hover:text-accent hover:underline">Perguntas
                            frequentes</a>
                        <a href="#"
                            class="text-muted text-sm transition duration-75 hover:text-accent hover:underline">Regras
                            do bolão</a>
                        <a href="#"
                            class="text-muted text-sm transition duration-75 hover:text-accent hover:underline">Fale
                            conosco</a>
                    </nav>
                </div>
                <div>
                    <h5 class="text-sm font-semibold tracking-wide text-accent mb-2">Legal</h5>
                    <nav class="flex flex-col gap-2">
                        <a href="#"
                            class="text-muted text-sm transition duration-75 hover:text-accent hover:underline">Termos
                            de uso</a>
                        <a href="#"
                            class="text-muted text-sm transition duration-75 hover:text-accent hover:underline">Política
                            de privacidade</a>

                    </nav>
                </div>
            </div>

            <span class="block w-full h-px bg-slate-800 my-6 md:my-8"></span>
            <div class="flex items-center justify-between text-xs text-muted">
                <p>© 2025 Oddly. Todos os direitos reservados.</p>
                <p>Oddly — onde palpite vira jogo.</p>

            </div>
        </div>
    </footer>
</x-layouts.app>
