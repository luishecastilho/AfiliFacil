import { Link } from '@inertiajs/react';

export function FinalCta() {
    return (
        <section className="relative overflow-hidden bg-[#0a0a0a]">
            <div
                className="pointer-events-none absolute inset-0 opacity-40"
                style={{
                    backgroundImage: 'radial-gradient(rgba(255,255,255,0.08) 1px, transparent 1px)',
                    backgroundSize: '28px 28px',
                }}
            />

            <div className="relative mx-auto max-w-4xl px-6 py-28 text-center lg:px-8 lg:py-36">
                <h2 className="font-display text-5xl font-bold leading-[1.05] tracking-tight text-white sm:text-6xl lg:text-7xl">
                    Este mês, suas notas fiscais levam{' '}
                    <em className="font-serif font-normal italic text-[#EE4D2D]">1 clique</em>
                </h2>
                <p className="mx-auto mt-6 max-w-xl text-lg text-neutral-400">
                    Crie sua conta gratuita, importe seu primeiro relatório e veja suas NFS-e sendo geradas —
                    sem cartão de crédito.
                </p>
                <div className="mt-12">
                    <Link
                        href={route('register')}
                        className="inline-block rounded-full bg-[#EE4D2D] px-10 py-5 text-base font-semibold text-white transition hover:scale-[1.03] hover:bg-[#D94426]"
                    >
                        Começar gratuitamente
                    </Link>
                </div>
            </div>
        </section>
    );
}
