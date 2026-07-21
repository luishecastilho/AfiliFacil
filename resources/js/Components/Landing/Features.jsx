import { Archive, History, Layers, RefreshCw, ShieldCheck, Users } from 'lucide-react';

/* Mini mock: commission rows grouping into a single nota. */
function GroupingMock() {
    return (
        <div className="mt-6 flex items-center gap-4" aria-hidden="true">
            <div className="flex-1 space-y-1.5">
                {['w-full', 'w-5/6', 'w-4/6'].map((width) => (
                    <div key={width} className={`h-2.5 rounded-full bg-neutral-200 ${width}`} />
                ))}
            </div>
            <span className="font-display text-lg font-bold text-neutral-300">→</span>
            <div className="rounded-xl border-2 border-[#EE4D2D]/40 bg-white px-4 py-3 shadow-sm">
                <p className="font-display text-xs font-bold text-neutral-900">1 NFS-e</p>
                <p className="text-[10px] text-neutral-400">por vendedor/mês</p>
            </div>
        </div>
    );
}

/* Mini mock: batch queue filling up. */
function BatchMock() {
    return (
        <div className="mt-6 space-y-2" aria-hidden="true">
            {[
                { width: 'w-full', color: 'bg-[#EE4D2D]' },
                { width: 'w-2/3', color: 'bg-[#EE4D2D]/60' },
                { width: 'w-1/3', color: 'bg-[#EE4D2D]/30' },
            ].map((bar) => (
                <div key={bar.width} className="h-2 w-full overflow-hidden rounded-full bg-neutral-200">
                    <div className={`h-full rounded-full ${bar.color} ${bar.width}`} />
                </div>
            ))}
        </div>
    );
}

const FEATURES = [
    {
        icon: Users,
        title: 'Uma nota por vendedor, automático',
        description: 'As comissões são agrupadas por vendedor e mês de referência, exatamente como a regra exige.',
        span: 'lg:col-span-2',
        mock: GroupingMock,
    },
    {
        icon: ShieldCheck,
        title: 'Validação antes da emissão',
        description: 'Dados conferidos e duplicatas detectadas antes de qualquer nota ser gerada.',
    },
    {
        icon: Archive,
        title: 'Download em ZIP',
        description: 'Todas as notas do mês em um único arquivo, prontas para arquivar ou enviar ao contador.',
    },
    {
        icon: Layers,
        title: 'Emissão em lote',
        description: 'Dezenas ou centenas de notas geradas em fila, sem travar e sem você acompanhar uma a uma.',
        span: 'lg:col-span-2',
        mock: BatchMock,
    },
    {
        icon: History,
        title: 'Histórico completo',
        description: 'Todas as importações e notas emitidas ficam salvas na sua conta.',
    },
    {
        icon: RefreshCw,
        title: 'Reprocessamento fácil',
        description: 'Alguma nota falhou? Reemita com um clique, sem refazer a importação.',
        span: 'lg:col-span-2',
    },
];

export function Features() {
    return (
        <section id="funcionalidades" className="bg-white py-24 lg:py-32">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="max-w-3xl">
                    <h2 className="font-display text-4xl font-bold tracking-tight text-neutral-900 sm:text-5xl">
                        Feito para quem é <em className="font-serif font-normal italic text-[#EE4D2D]">afiliado</em>,
                        não contador
                    </h2>
                    <p className="mt-4 text-lg text-neutral-500">
                        Você não precisa entender de nota fiscal. O AfiliFacil cuida da parte chata.
                    </p>
                </div>

                <div className="mt-16 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    {FEATURES.map((feature) => (
                        <div
                            key={feature.title}
                            className={`rounded-3xl bg-neutral-50 p-8 transition hover:-translate-y-1 hover:shadow-lg hover:shadow-[#EE4D2D]/5 ${feature.span ?? ''}`}
                        >
                            <span className="flex h-10 w-10 items-center justify-center rounded-xl bg-[#EE4D2D]/10 text-[#EE4D2D]">
                                <feature.icon className="h-5 w-5" />
                            </span>
                            <h3 className="mt-5 font-display text-xl font-bold text-neutral-900">{feature.title}</h3>
                            <p className="mt-2 text-sm leading-relaxed text-neutral-500">{feature.description}</p>
                            {feature.mock && <feature.mock />}
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
