import { Archive, History, Layers, RefreshCw, ShieldCheck, Users } from 'lucide-react';

const FEATURES = [
    {
        icon: Users,
        title: 'Uma nota por vendedor, automático',
        description: 'As comissões são agrupadas por vendedor e mês de referência, exatamente como a regra exige.',
    },
    {
        icon: ShieldCheck,
        title: 'Validação antes da emissão',
        description: 'Dados conferidos e duplicatas detectadas antes de qualquer nota ser gerada.',
    },
    {
        icon: Layers,
        title: 'Emissão em lote',
        description: 'Dezenas ou centenas de notas geradas em fila, sem travar e sem você acompanhar uma a uma.',
    },
    {
        icon: Archive,
        title: 'Download em ZIP',
        description: 'Todas as notas do mês em um único arquivo, prontas para arquivar ou enviar ao contador.',
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
    },
];

export function Features() {
    return (
        <section id="funcionalidades" className="bg-white py-24">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="mx-auto max-w-2xl text-center">
                    <h2 className="text-3xl font-bold tracking-tight text-neutral-900 sm:text-4xl">
                        Feito para quem é afiliado, não contador
                    </h2>
                    <p className="mt-4 text-lg text-neutral-500">
                        Você não precisa entender de nota fiscal. O AfiliFacil cuida da parte chata.
                    </p>
                </div>

                <div className="mt-16 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    {FEATURES.map((feature) => (
                        <div
                            key={feature.title}
                            className="rounded-2xl border border-neutral-200 bg-white p-6 transition hover:border-[#EE4D2D]/40 hover:shadow-lg hover:shadow-[#EE4D2D]/5"
                        >
                            <span className="flex h-10 w-10 items-center justify-center rounded-lg bg-[#EE4D2D]/10 text-[#EE4D2D]">
                                <feature.icon className="h-5 w-5" />
                            </span>
                            <h3 className="mt-4 text-base font-semibold text-neutral-900">{feature.title}</h3>
                            <p className="mt-2 text-sm leading-relaxed text-neutral-500">{feature.description}</p>
                        </div>
                    ))}
                </div>
            </div>
        </section>
    );
}
