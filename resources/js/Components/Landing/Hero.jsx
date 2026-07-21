import { Link } from '@inertiajs/react';
import { ArrowDown } from 'lucide-react';

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

const FORMAT_CHIPS = ['CSV', 'XLSX', 'XML', 'ZIP'];

/* PHOTO SLOT: either floating card below can be swapped for a real photo (e.g. from magnific.com) dropped in public/images/. */

/* Floating mock: mini NFS-e document (tilted right). */
function NfseCard() {
    return (
        <div className="absolute -right-12 bottom-28 hidden rotate-6 lg:block xl:-right-20">
            <div className="w-48 rounded-2xl bg-white p-4 shadow-2xl shadow-black/40 animate-float-sway">
                <div className="flex items-center justify-between">
                    <span className="font-display text-xs font-bold tracking-wide text-neutral-900">NFS-e</span>
                    <span className="rounded-full bg-[#EE4D2D]/10 px-2 py-0.5 text-[10px] font-semibold text-[#EE4D2D] ring-1 ring-inset ring-[#EE4D2D]/30">
                        Emitida
                    </span>
                </div>
                <p className="mt-1 text-[10px] text-neutral-400">Nº 000.042 · Julho/2026</p>
                <div className="mt-4 space-y-2">
                    <div>
                        <p className="text-[10px] uppercase tracking-wide text-neutral-400">Tomador</p>
                        <p className="text-xs font-medium text-neutral-900">Loja Bella Moda</p>
                    </div>
                    <div>
                        <p className="text-[10px] uppercase tracking-wide text-neutral-400">Valor do serviço</p>
                        <p className="font-display text-lg font-bold text-neutral-900">R$ 1.284,50</p>
                    </div>
                </div>
            </div>
        </div>
    );
}

/* Floating mock: batch stat card (tilted left). */
function StatCard() {
    return (
        <div className="absolute -left-8 bottom-10 hidden -rotate-3 lg:block xl:left-2">
            <div className="w-52 rounded-2xl border border-white/10 bg-[#161618] p-5 shadow-2xl shadow-black/40 animate-float-delayed">
                <p className="font-display text-4xl font-bold text-white">
                    83<span className="text-[#EE4D2D]">.</span>
                </p>
                <p className="mt-1 text-sm text-neutral-400">
                    notas emitidas <em className="font-serif italic text-[#EE4D2D]">em 1 clique</em>
                </p>
                <div className="mt-3 h-1.5 w-full overflow-hidden rounded-full bg-white/10">
                    <div className="h-full w-[92%] rounded-full bg-[#EE4D2D]" />
                </div>
            </div>
        </div>
    );
}

export function Hero() {
    return (
        <section className="relative overflow-hidden bg-[#0a0a0a]">
            <div
                className="pointer-events-none absolute inset-0 opacity-40"
                style={{
                    backgroundImage: 'radial-gradient(rgba(255,255,255,0.08) 1px, transparent 1px)',
                    backgroundSize: '28px 28px',
                }}
            />

            <div className="relative mx-auto max-w-7xl px-6 pb-24 pt-32 lg:px-8 lg:pt-40">
                <div className="relative mx-auto max-w-4xl text-center">
                    <NfseCard />
                    <StatCard />

                    <h1 className="font-display text-5xl font-bold leading-[1.02] tracking-tight text-white sm:text-6xl lg:text-7xl xl:text-8xl">
                        Todas as <em className="font-serif font-normal italic">notas fiscais</em> das suas comissões
                        Shopee em <em className="font-serif font-normal italic text-[#EE4D2D]">1 clique</em>
                    </h1>

                    <p className="mx-auto mt-8 max-w-2xl text-lg leading-relaxed text-neutral-400">
                        Agora todo afiliado precisa emitir uma NFS-e para cada vendedor que pagou comissão extra, e
                        fazer isso na mão, uma por uma, leva horas todo mês. Com o AfiliFacil, você importa o
                        relatório da Shopee e o sistema gera todas as notas de uma vez. Rápido, fácil e automático.
                    </p>

                    <div className="mt-10 flex flex-col items-center justify-center gap-6 sm:flex-row">
                        <Link
                            href={route('register')}
                            className="w-full rounded-full bg-[#EE4D2D] px-8 py-4 text-base font-semibold text-white transition hover:scale-[1.03] hover:bg-[#D94426] sm:w-auto"
                        >
                            Começar gratuitamente
                        </Link>
                        <a
                            href="#como-funciona"
                            className="inline-flex items-center gap-2 text-sm font-semibold text-white transition hover:text-[#EE4D2D]"
                        >
                            Ver como funciona
                            <ArrowDown className="h-4 w-4" />
                        </a>
                    </div>

                    <div className="mt-10 flex flex-wrap items-center justify-center gap-2">
                        {FORMAT_CHIPS.map((chip) => (
                            <span
                                key={chip}
                                className="rounded-full border border-white/15 px-3 py-1 text-xs font-medium text-neutral-400"
                            >
                                {chip}
                            </span>
                        ))}
                    </div>
                </div>

                <div className="relative mx-auto mt-24 max-w-4xl -rotate-1">
                    <div className="overflow-hidden rounded-xl border border-white/10 border-t-2 border-t-[#EE4D2D] bg-[#111113] shadow-2xl shadow-[#EE4D2D]/10">
                        <div className="flex items-center gap-2 border-b border-white/10 px-4 py-3">
                            <span className="h-3 w-3 rounded-full bg-red-500/60" />
                            <span className="h-3 w-3 rounded-full bg-yellow-500/60" />
                            <span className="h-3 w-3 rounded-full bg-[#EE4D2D]/60" />
                            <span className="ml-3 text-xs text-neutral-500">afilifacil.com.br/imports/42</span>
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
