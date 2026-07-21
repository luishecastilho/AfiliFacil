import { Link } from '@inertiajs/react';

export function FinalCta() {
    return (
        <section className="relative overflow-hidden bg-[#0a0a0a]">
            <div className="pointer-events-none absolute inset-0">
                <div className="absolute left-1/2 top-1/2 h-[24rem] w-[24rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-[#EE4D2D]/20 blur-[120px]" />
            </div>

            <div className="relative mx-auto max-w-3xl px-6 py-24 text-center lg:px-8">
                <h2 className="text-3xl font-bold tracking-tight text-white sm:text-4xl">
                    Este mês, suas notas fiscais levam <span className="text-[#EE4D2D]">1 clique</span>
                </h2>
                <p className="mx-auto mt-4 max-w-xl text-lg text-neutral-400">
                    Crie sua conta gratuita, importe seu primeiro relatório e veja suas NFS-e sendo geradas —
                    sem cartão de crédito.
                </p>
                <div className="mt-10">
                    <Link
                        href={route('register')}
                        className="inline-block rounded-full bg-[#EE4D2D] px-8 py-3 text-sm font-semibold text-white transition hover:bg-[#D94426]"
                    >
                        Começar gratuitamente
                    </Link>
                </div>
            </div>
        </section>
    );
}
