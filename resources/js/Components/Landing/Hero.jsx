import { Link } from '@inertiajs/react';

const MOCK_ROWS = [
    { name: 'Loja Bella Moda', document: '12.345.678/0001-90', amount: 'R$ 1.284,50', status: 'generated' },
    { name: 'Casa & Decoração RS', document: '98.765.432/0001-11', amount: 'R$ 842,10', status: 'generated' },
    { name: 'TechShop Distribuidora', document: '45.612.789/0001-22', amount: 'R$ 3.910,75', status: 'processing' },
    { name: 'Pet World Comércio', document: '11.222.333/0001-44', amount: 'R$ 560,00', status: 'queued' },
];

const STATUS_BADGE = {
    generated: { label: 'Emitida', className: 'bg-[#EE4D2D]/10 text-[#EE4D2D] ring-[#EE4D2D]/30' },
    processing: { label: 'Processando', className: 'bg-amber-500/10 text-amber-400 ring-amber-500/30' },
    queued: { label: 'Na fila', className: 'bg-neutral-500/10 text-neutral-400 ring-neutral-500/30' },
};

export function Hero() {
    return (
        <section className="relative overflow-hidden bg-[#0a0a0a]">
            <div className="pointer-events-none absolute inset-0">
                <div className="absolute left-1/2 top-[-10rem] h-[36rem] w-[36rem] -translate-x-1/2 rounded-full bg-[#EE4D2D]/20 blur-[120px]" />
            </div>

            <div className="relative mx-auto max-w-7xl px-6 pb-24 pt-20 lg:px-8 lg:pt-28">
                <div className="mx-auto max-w-3xl text-center">
                    <span className="inline-flex items-center gap-2 rounded-full border border-white/10 bg-white/5 px-3 py-1 text-xs font-medium text-[#EE4D2D]">
                        Feito para afiliados Shopee
                    </span>

                    <h1 className="mt-6 text-5xl font-extrabold tracking-tight text-white sm:text-6xl">
                        Todas as notas fiscais das suas comissões Shopee em <span className="text-[#EE4D2D]">1 clique</span>
                    </h1>

                    <p className="mx-auto mt-6 max-w-2xl text-lg leading-relaxed text-neutral-400">
                        Agora todo afiliado precisa emitir uma NFS-e para cada vendedor que pagou comissão extra — e
                        fazer isso na mão, uma por uma, leva horas todo mês. Com o AfiliFacil, você importa o
                        relatório da Shopee e o sistema gera todas as notas de uma vez. Rápido, fácil e automático.
                    </p>

                    <div className="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <Link
                            href={route('register')}
                            className="w-full rounded-full bg-[#EE4D2D] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#D94426] sm:w-auto"
                        >
                            Começar gratuitamente
                        </Link>
                        <a
                            href="#como-funciona"
                            className="w-full rounded-full border border-white/15 px-6 py-3 text-sm font-semibold text-white transition hover:bg-white/5 sm:w-auto"
                        >
                            Ver como funciona
                        </a>
                    </div>
                </div>

                <div className="relative mx-auto mt-20 max-w-4xl">
                    <div className="overflow-hidden rounded-xl border border-white/10 bg-[#111113] shadow-2xl shadow-[#EE4D2D]/10">
                        <div className="flex items-center gap-2 border-b border-white/10 px-4 py-3">
                            <span className="h-3 w-3 rounded-full bg-red-500/60" />
                            <span className="h-3 w-3 rounded-full bg-yellow-500/60" />
                            <span className="h-3 w-3 rounded-full bg-[#EE4D2D]/60" />
                            <span className="ml-3 text-xs text-neutral-500">afilifacil.app/imports/42</span>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="w-full min-w-[640px] text-left text-sm">
                                <thead>
                                    <tr className="border-b border-white/10 text-xs uppercase tracking-wide text-neutral-500">
                                        <th className="px-5 py-3 font-medium">Vendedor</th>
                                        <th className="px-5 py-3 font-medium">CNPJ</th>
                                        <th className="px-5 py-3 font-medium">Comissão</th>
                                        <th className="px-5 py-3 font-medium">Status</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-white/5">
                                    {MOCK_ROWS.map((row) => {
                                        const badge = STATUS_BADGE[row.status];

                                        return (
                                            <tr key={row.document} className="text-neutral-300">
                                                <td className="whitespace-nowrap px-5 py-3.5 font-medium text-white">{row.name}</td>
                                                <td className="whitespace-nowrap px-5 py-3.5 text-neutral-500">{row.document}</td>
                                                <td className="whitespace-nowrap px-5 py-3.5">{row.amount}</td>
                                                <td className="whitespace-nowrap px-5 py-3.5">
                                                    <span
                                                        className={`inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium ring-1 ring-inset ${badge.className}`}
                                                    >
                                                        <span className="h-1.5 w-1.5 rounded-full bg-current" />
                                                        {badge.label}
                                                    </span>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    );
}
