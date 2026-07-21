import { Link } from '@inertiajs/react';
import { CheckCircle2, Download, LayoutGrid, Upload } from 'lucide-react';

const STEPS = [
    {
        icon: Upload,
        title: 'Importe seu relatório',
        description: 'Baixe o relatório de comissões na sua conta Shopee e envie o arquivo (CSV) para o AfiliFacil.',
    },
    {
        icon: CheckCircle2,
        title: 'Validação automática',
        description:
            'O sistema confere os dados de cada vendedor, aponta erros antes da emissão e agrupa as comissões por vendedor e mês.',
    },
    {
        icon: LayoutGrid,
        title: 'Emissão em lote',
        description:
            'Um clique e todas as NFS-e entram na fila de emissão automaticamente — uma nota por vendedor, sem você digitar nada.',
    },
    {
        icon: Download,
        title: 'Baixe tudo pronto',
        description:
            'PDF e XML de cada nota, individual ou em um ZIP com tudo. Seu histórico fica salvo para consultar quando precisar.',
    },
];

export function HowItWorks() {
    return (
        <section id="como-funciona" className="bg-white py-24">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="mx-auto max-w-2xl text-center">
                    <h2 className="text-3xl font-bold tracking-tight text-neutral-900 sm:text-4xl">
                        Do relatório da Shopee à nota fiscal emitida, em minutos
                    </h2>
                    <p className="mt-4 text-lg text-neutral-500">
                        Um fluxo simples, pensado para quem nunca emitiu nota fiscal na vida.
                    </p>
                </div>

                <div className="mt-16 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                    {STEPS.map((step, index) => (
                        <div
                            key={step.title}
                            className="group relative rounded-2xl border border-neutral-200 bg-white p-6 transition hover:border-[#EE4D2D]/40 hover:shadow-lg hover:shadow-[#EE4D2D]/5"
                        >
                            <div className="flex items-center gap-3">
                                <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#EE4D2D]/10 text-[#EE4D2D]">
                                    <step.icon className="h-5 w-5" />
                                </span>
                                <span className="text-xs font-semibold text-neutral-400">Passo {index + 1}</span>
                            </div>

                            <h3 className="mt-4 text-base font-semibold text-neutral-900">{step.title}</h3>
                            <p className="mt-2 text-sm leading-relaxed text-neutral-500">{step.description}</p>
                        </div>
                    ))}
                </div>

                <div className="mt-16 flex justify-center">
                    <Link
                        href={route('register')}
                        className="rounded-full bg-neutral-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#D94426]"
                    >
                        Começar gratuitamente
                    </Link>
                </div>
            </div>
        </section>
    );
}
