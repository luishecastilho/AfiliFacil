import { Head } from '@inertiajs/react';
import { Header } from '@/Components/Landing/Header';
import { Footer } from '@/Components/Landing/Footer';

export function LegalLayout({ title, metaDescription, lastUpdated, children }) {
    return (
        <>
            <Head title={`${title} — AfiliFacil`}>
                {metaDescription && <meta name="description" content={metaDescription} />}
            </Head>

            <div className="bg-white font-sans text-neutral-900 antialiased">
                <Header />

                <section className="relative overflow-hidden bg-[#0a0a0a]">
                    <div
                        className="pointer-events-none absolute inset-0 opacity-40"
                        style={{
                            backgroundImage: 'radial-gradient(rgba(255,255,255,0.08) 1px, transparent 1px)',
                            backgroundSize: '28px 28px',
                        }}
                    />
                    <div className="relative mx-auto max-w-3xl px-6 pb-16 pt-32 lg:px-8 lg:pt-40">
                        <h1 className="font-display text-4xl font-bold tracking-tight text-white sm:text-5xl">
                            {title}
                        </h1>
                        {lastUpdated && (
                            <p className="mt-4 text-sm text-neutral-400">Última atualização: {lastUpdated}</p>
                        )}
                    </div>
                </section>

                <div className="bg-white py-16 lg:py-20">
                    <article className="legal-prose mx-auto max-w-3xl px-6 lg:px-8">{children}</article>
                </div>

                <Footer />
            </div>
        </>
    );
}
