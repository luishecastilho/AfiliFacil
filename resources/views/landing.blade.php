<!DOCTYPE html>
<html lang="pt-BR">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'NF Facilitator') }} — Emissão de NF-e para afiliados Shopee</title>
        <meta name="description" content="Automatize a emissão de notas fiscais para afiliados Shopee. Upload do relatório, validação automática e geração em lote.">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css'])
    </head>
    <body class="bg-white font-sans text-neutral-900 antialiased">

        {{-- ============ HEADER ============ --}}
        <header class="sticky top-0 z-50 border-b border-white/10 bg-[#0a0a0a]/80 backdrop-blur-md">
            <nav class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6 lg:px-8">
                <a href="{{ route('landing') }}" class="flex items-center gap-2">
                    <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-400">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                            <path d="M13 2 3 14h7l-1 8 11-14h-7z" />
                        </svg>
                    </span>
                    <span class="text-base font-semibold tracking-tight text-white">NF Facilitator</span>
                </a>

                <div class="hidden items-center gap-8 md:flex">
                    <a href="#como-funciona" class="text-sm text-neutral-300 transition hover:text-white">Como funciona</a>
                    <a href="#funcionalidades" class="text-sm text-neutral-300 transition hover:text-white">Funcionalidades</a>
                </div>

                <div class="hidden items-center gap-3 md:flex">
                    <a href="{{ route('login') }}" class="text-sm font-medium text-neutral-300 transition hover:text-white">Entrar</a>
                    <a href="{{ route('register') }}" class="rounded-full bg-emerald-500 px-4 py-2 text-sm font-semibold text-black transition hover:bg-emerald-400">
                        Começar grátis
                    </a>
                </div>

                <button
                    type="button"
                    id="menu-toggle"
                    class="inline-flex items-center justify-center rounded-md p-2 text-neutral-300 hover:bg-white/5 hover:text-white md:hidden"
                    aria-label="Abrir menu"
                    aria-expanded="false"
                >
                    <svg id="icon-open" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-6 w-6">
                        <path d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <svg id="icon-close" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="hidden h-6 w-6">
                        <path d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </nav>

            <div id="mobile-menu" class="hidden border-t border-white/10 bg-[#0a0a0a] px-6 py-4 md:hidden">
                <div class="flex flex-col gap-4">
                    <a href="#como-funciona" class="text-sm text-neutral-300 hover:text-white">Como funciona</a>
                    <a href="#funcionalidades" class="text-sm text-neutral-300 hover:text-white">Funcionalidades</a>
                    <a href="{{ route('login') }}" class="text-sm font-medium text-neutral-300 hover:text-white">Entrar</a>
                    <a href="{{ route('register') }}" class="w-full rounded-full bg-emerald-500 px-4 py-2 text-center text-sm font-semibold text-black hover:bg-emerald-400">
                        Começar grátis
                    </a>
                </div>
            </div>
        </header>

        {{-- ============ HERO ============ --}}
        <section class="relative overflow-hidden bg-[#0a0a0a]">
            <div class="pointer-events-none absolute inset-0">
                <div class="absolute left-1/2 top-[-10rem] h-[36rem] w-[36rem] -translate-x-1/2 rounded-full bg-emerald-500/20 blur-[120px]"></div>
            </div>

            <div class="relative mx-auto max-w-7xl px-6 pb-24 pt-20 lg:px-8 lg:pt-28">
                <div class="mx-auto max-w-3xl text-center">
                    <span class="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-medium text-emerald-400">
                        Feito para afiliados Shopee
                    </span>

                    <h1 class="mt-6 text-5xl font-extrabold tracking-tight text-white sm:text-6xl">
                        Chega de emitir NF-e
                        <span class="text-emerald-400">manualmente</span>
                        para afiliados Shopee
                    </h1>

                    <p class="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-neutral-400">
                        Seus afiliados recebem comissões todo mês — e alguém precisa emitir uma nota fiscal para
                        cada um deles. Para centenas de vendedores, isso são horas de trabalho manual repetitivo,
                        todo santo mês. O NF Facilitator automatiza esse processo do início ao fim.
                    </p>

                    <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="{{ route('register') }}" class="w-full rounded-full bg-emerald-500 px-6 py-3 text-sm font-semibold text-black transition hover:bg-emerald-400 sm:w-auto">
                            Começar gratuitamente
                        </a>
                        <a href="#como-funciona" class="w-full rounded-full border border-white/15 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/5 sm:w-auto">
                            Ver como funciona
                        </a>
                    </div>
                </div>

                {{-- Mock dashboard preview --}}
                <div class="relative mx-auto mt-20 max-w-4xl">
                    <div class="overflow-hidden rounded-xl border border-white/10 bg-[#111113] shadow-2xl shadow-emerald-500/10">
                        <div class="flex items-center gap-2 border-b border-white/10 px-4 py-3">
                            <span class="h-3 w-3 rounded-full bg-red-500/60"></span>
                            <span class="h-3 w-3 rounded-full bg-yellow-500/60"></span>
                            <span class="h-3 w-3 rounded-full bg-emerald-500/60"></span>
                            <span class="ml-3 text-xs text-neutral-500">nf-facilitator.app/imports/42</span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[640px] text-left text-sm">
                                <thead>
                                    <tr class="border-b border-white/10 text-xs uppercase tracking-wide text-neutral-500">
                                        <th class="px-5 py-3 font-medium">Vendedor</th>
                                        <th class="px-5 py-3 font-medium">CNPJ</th>
                                        <th class="px-5 py-3 font-medium">Comissão</th>
                                        <th class="px-5 py-3 font-medium">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    @php
                                        $rows = [
                                            ['Loja Bella Moda', '12.345.678/0001-90', 'R$ 1.284,50', 'generated'],
                                            ['Casa & Decoração RS', '98.765.432/0001-11', 'R$ 842,10', 'generated'],
                                            ['TechShop Distribuidora', '45.612.789/0001-22', 'R$ 3.910,75', 'processing'],
                                            ['Pet World Comércio', '11.222.333/0001-44', 'R$ 560,00', 'queued'],
                                        ];
                                        $badge = [
                                            'generated' => ['Emitida', 'bg-emerald-500/10 text-emerald-400 ring-emerald-500/30'],
                                            'processing' => ['Processando', 'bg-amber-500/10 text-amber-400 ring-amber-500/30'],
                                            'queued' => ['Na fila', 'bg-neutral-500/10 text-neutral-400 ring-neutral-500/30'],
                                        ];
                                    @endphp
                                    @foreach ($rows as [$name, $doc, $amount, $status])
                                        <tr class="text-neutral-300">
                                            <td class="whitespace-nowrap px-5 py-3.5 font-medium text-white">{{ $name }}</td>
                                            <td class="whitespace-nowrap px-5 py-3.5 text-neutral-500">{{ $doc }}</td>
                                            <td class="whitespace-nowrap px-5 py-3.5">{{ $amount }}</td>
                                            <td class="whitespace-nowrap px-5 py-3.5">
                                                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset {{ $badge[$status][1] }}">
                                                    <span class="h-1.5 w-1.5 rounded-full bg-current"></span>
                                                    {{ $badge[$status][0] }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ============ HOW WE SOLVE IT ============ --}}
        <section id="como-funciona" class="bg-white py-24">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <h2 id="funcionalidades" class="text-3xl font-bold tracking-tight text-neutral-900 sm:text-4xl">
                        Do relatório da Shopee à nota fiscal, em minutos
                    </h2>
                    <p class="mt-4 text-lg text-neutral-500">
                        Um fluxo simples que elimina o trabalho manual de emitir centenas de notas fiscais todo mês.
                    </p>
                </div>

                <div class="mt-16 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    @php
                        $steps = [
                            [
                                'title' => 'Upload do relatório',
                                'description' => 'Baixe o relatório de comissões da Shopee e faça o upload em segundos.',
                                'icon' => '<path d="M12 16V4m0 0 4 4m-4-4-4 4"/><path d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/>',
                            ],
                            [
                                'title' => 'Validação automática',
                                'description' => 'O sistema valida os dados dos afiliados, detecta duplicatas e agrupa por CNPJ e mês de referência.',
                                'icon' => '<path d="m9 12 2 2 4-4"/><circle cx="12" cy="12" r="10"/>',
                            ],
                            [
                                'title' => 'Geração em lote',
                                'description' => 'Com um clique, todas as NF-e são geradas automaticamente em fila, sem travar o sistema.',
                                'icon' => '<rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/>',
                            ],
                            [
                                'title' => 'Download pronto',
                                'description' => 'Baixe as notas individuais ou um ZIP com tudo de uma vez. Histórico sempre disponível.',
                                'icon' => '<path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><path d="m7 10 5 5 5-5"/><path d="M12 15V3"/>',
                            ],
                        ];
                    @endphp

                    @foreach ($steps as $index => $step)
                        <div class="group relative rounded-2xl border border-neutral-200 bg-white p-6 transition hover:border-emerald-500/40 hover:shadow-lg hover:shadow-emerald-500/5">
                            <div class="flex items-center gap-3">
                                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-5 w-5">
                                        {!! $step['icon'] !!}
                                    </svg>
                                </span>
                                <span class="text-xs font-semibold text-neutral-400">Passo {{ $index + 1 }}</span>
                            </div>

                            <h3 class="mt-4 text-base font-semibold text-neutral-900">{{ $step['title'] }}</h3>
                            <p class="mt-2 text-sm leading-relaxed text-neutral-500">{{ $step['description'] }}</p>
                        </div>
                    @endforeach
                </div>

                <div class="mt-16 flex justify-center">
                    <a href="{{ route('register') }}" class="rounded-full bg-neutral-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-emerald-600">
                        Começar gratuitamente
                    </a>
                </div>
            </div>
        </section>

        {{-- ============ FOOTER ============ --}}
        <footer class="border-t border-neutral-200 bg-white py-8">
            <div class="mx-auto flex max-w-7xl flex-col items-center justify-between gap-4 px-6 sm:flex-row lg:px-8">
                <div class="flex items-center gap-2">
                    <span class="flex h-6 w-6 items-center justify-center rounded-md bg-emerald-500/10 text-emerald-600">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="h-3.5 w-3.5">
                            <path d="M13 2 3 14h7l-1 8 11-14h-7z" />
                        </svg>
                    </span>
                    <span class="text-sm font-medium text-neutral-600">NF Facilitator</span>
                </div>
                <p class="text-xs text-neutral-400">&copy; {{ date('Y') }} NF Facilitator. Todos os direitos reservados.</p>
            </div>
        </footer>

        <script>
            const menuToggle = document.getElementById('menu-toggle');
            const mobileMenu = document.getElementById('mobile-menu');
            const iconOpen = document.getElementById('icon-open');
            const iconClose = document.getElementById('icon-close');

            menuToggle.addEventListener('click', () => {
                const isOpen = !mobileMenu.classList.contains('hidden');

                mobileMenu.classList.toggle('hidden');
                iconOpen.classList.toggle('hidden');
                iconClose.classList.toggle('hidden');
                menuToggle.setAttribute('aria-expanded', String(!isOpen));
            });
        </script>
    </body>
</html>
