import { Link } from '@inertiajs/react';
import { CheckCircle2, Download, LayoutGrid, Upload } from 'lucide-react';

const STEPS = [
    {
        icon: Upload,
        title: 'Upload do relatório',
        description: 'Baixe o relatório de comissões da Shopee e faça o upload em segundos.',
    },
    {
        icon: CheckCircle2,
        title: 'Validação automática',
        description: 'O sistema valida os dados dos afiliados, detecta duplicatas e agrupa por CNPJ e mês de referência.',
    },
    {
        icon: LayoutGrid,
        title: 'Geração em lote',
        description: 'Com um clique, todas as NF-e são geradas automaticamente em fila, sem travar o sistema.',
    },
    {
        icon: Download,
        title: 'Download pronto',
        description: 'Baixe as notas individuais ou um ZIP com tudo de uma vez. Histórico sempre disponível.',
    },
];

export function HowItWorks() {
    return (
        <section id="como-funciona" className="bg-white py-24">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="mx-auto max-w-2xl text-center">
                    <h2 id="funcionalidades" className="text-3xl font-bold tracking-tight text-neutral-900 sm:text-4xl">
                        Do relatório da Shopee à nota fiscal, em minutos
                    </h2>
                    <p className="mt-4 text-lg text-neutral-500">
                        Um fluxo simples que elimina o trabalho manual de emitir centenas de notas fiscais todo mês.
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
