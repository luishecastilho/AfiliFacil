import { CalendarX2, FileWarning, Timer } from 'lucide-react';

const PAINS = [
    {
        icon: Timer,
        title: 'Horas no portal da prefeitura',
        description:
            'Preencher os dados de cada vendedor, um formulário por vez, repetindo o processo dezenas de vezes.',
    },
    {
        icon: FileWarning,
        title: 'Um erro de CNPJ e a nota volta',
        description:
            'Digitou um dígito errado? Nota rejeitada, retrabalho e risco de pendência com o vendedor.',
    },
    {
        icon: CalendarX2,
        title: 'Recomeçar do zero no mês seguinte',
        description:
            'O relatório muda, os vendedores mudam, e o trabalho manual recomeça a cada fechamento.',
    },
];

export function Problem() {
    return (
        <section className="bg-neutral-50 py-24">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="mx-auto max-w-2xl text-center">
                    <h2 className="text-3xl font-bold tracking-tight text-neutral-900 sm:text-4xl">
                        Uma nota fiscal para cada vendedor. Todo santo mês.
                    </h2>
                    <p className="mt-4 text-lg text-neutral-500">
                        Pela nova regra, toda venda com comissão extra exige uma nota fiscal emitida para o
                        vendedor que pagou essa comissão. Se você recebeu comissão extra de 80 lojas, são 80
                        notas para emitir.
                    </p>
                </div>

                <div className="mt-16 grid grid-cols-1 gap-6 sm:grid-cols-3">
                    {PAINS.map((pain) => (
                        <div key={pain.title} className="rounded-2xl border border-neutral-200 bg-white p-6">
                            <span className="flex h-10 w-10 items-center justify-center rounded-lg bg-[#EE4D2D]/10 text-[#EE4D2D]">
                                <pain.icon className="h-5 w-5" />
                            </span>
                            <h3 className="mt-4 text-base font-semibold text-neutral-900">{pain.title}</h3>
                            <p className="mt-2 text-sm leading-relaxed text-neutral-500">{pain.description}</p>
                        </div>
                    ))}
                </div>

                <p className="mt-12 text-center text-lg font-semibold text-neutral-900">
                    O AfiliFacil existe para transformar essas horas em <span className="text-[#EE4D2D]">um clique</span>.
                </p>
            </div>
        </section>
    );
}
