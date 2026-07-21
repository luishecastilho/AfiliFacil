const PAINS = [
    {
        title: 'Horas no portal da prefeitura',
        description:
            'Preencher os dados de cada vendedor, um formulário por vez, repetindo o processo dezenas de vezes.',
    },
    {
        title: 'Um erro de CNPJ e a nota volta',
        description:
            'Digitou um dígito errado? Nota rejeitada, retrabalho e risco de pendência com o vendedor.',
    },
    {
        title: 'Recomeçar do zero no mês seguinte',
        description:
            'O relatório muda, os vendedores mudam, e o trabalho manual recomeça a cada fechamento.',
    },
];

export function Problem() {
    return (
        <section className="bg-neutral-50 py-24 lg:py-32">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="grid grid-cols-1 gap-16 lg:grid-cols-2 lg:gap-20">
                    <div>
                        <h2 className="font-display text-4xl font-bold tracking-tight text-neutral-900 sm:text-5xl">
                            Uma nota fiscal para <em className="font-serif font-normal italic text-[#EE4D2D]">cada vendedor</em>.
                            Todo santo mês.
                        </h2>
                        <p className="mt-6 text-lg leading-relaxed text-neutral-500">
                            Pela nova regra, toda venda com comissão extra exige uma nota fiscal emitida para o
                            vendedor que pagou essa comissão. Se você recebeu comissão extra de 80 lojas, são 80
                            notas para emitir.
                        </p>
                        <p className="mt-10 font-serif text-2xl italic leading-snug text-neutral-900 sm:text-3xl">
                            O AfiliFacil existe para transformar essas horas em{' '}
                            <span className="text-[#EE4D2D]">um clique</span>.
                        </p>
                    </div>

                    <div>
                        {PAINS.map((pain, index) => (
                            <div
                                key={pain.title}
                                className="flex gap-6 border-t border-neutral-200 py-8 first:border-t-0 first:pt-0 lg:first:pt-8 lg:first:border-t"
                            >
                                <span className="text-stroke font-display text-6xl font-bold leading-none text-neutral-400 sm:text-7xl">
                                    {String(index + 1).padStart(2, '0')}
                                </span>
                                <div>
                                    <h3 className="font-display text-xl font-bold text-neutral-900">{pain.title}</h3>
                                    <p className="mt-2 text-sm leading-relaxed text-neutral-500">{pain.description}</p>
                                </div>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}
