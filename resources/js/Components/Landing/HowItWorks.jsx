import { Link } from '@inertiajs/react';

const STEPS = [
    {
        title: 'Importe seu relatório',
        description: 'Baixe o relatório de comissões na sua conta Shopee e envie o arquivo (CSV) para o AfiliFacil.',
    },
    {
        title: 'Validação automática',
        description:
            'O sistema confere os dados de cada vendedor, aponta erros antes da emissão e agrupa as comissões por vendedor e mês.',
    },
    {
        title: 'Emissão em lote',
        description:
            'Um clique e todas as NFS-e entram na fila de emissão automaticamente — uma nota por vendedor, sem você digitar nada.',
    },
    {
        title: 'Baixe tudo pronto',
        description:
            'PDF e XML de cada nota, individual ou em um ZIP com tudo. Seu histórico fica salvo para consultar quando precisar.',
    },
];

export function HowItWorks() {
    return (
        <section id="como-funciona" className="bg-white py-24 lg:py-32">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="max-w-3xl">
                    <h2 className="font-display text-4xl font-bold tracking-tight text-neutral-900 sm:text-5xl">
                        Do relatório da Shopee à nota fiscal emitida,{' '}
                        <em className="font-serif font-normal italic text-[#EE4D2D]">em minutos</em>
                    </h2>
                    <p className="mt-4 text-lg text-neutral-500">
                        Um fluxo simples, pensado para quem nunca emitiu nota fiscal na vida.
                    </p>
                </div>

                <div className="mt-16 border-t border-neutral-200">
                    {STEPS.map((step, index) => (
                        <div
                            key={step.title}
                            className="group grid grid-cols-[auto,1fr] items-baseline gap-x-6 gap-y-2 border-b border-neutral-200 py-8 transition hover:bg-neutral-50 sm:grid-cols-[8rem,1fr,1.5fr] sm:gap-x-10 lg:px-4"
                        >
                            <span className="text-stroke font-display text-5xl font-bold leading-none text-neutral-300 transition group-hover:text-[#EE4D2D] sm:text-6xl">
                                {String(index + 1).padStart(2, '0')}
                            </span>
                            <h3 className="font-display text-2xl font-bold text-neutral-900">{step.title}</h3>
                            <p className="col-span-2 text-base leading-relaxed text-neutral-500 sm:col-span-1">
                                {step.description}
                            </p>
                        </div>
                    ))}
                </div>

                <div className="mt-16 flex justify-center">
                    <Link
                        href={route('register')}
                        className="rounded-full bg-[#EE4D2D] px-8 py-4 text-base font-semibold text-white transition hover:scale-[1.03] hover:bg-[#D94426]"
                    >
                        Começar gratuitamente
                    </Link>
                </div>
            </div>
        </section>
    );
}
