import { Head } from '@inertiajs/react';
import { Header } from '@/Components/Landing/Header';
import { Hero } from '@/Components/Landing/Hero';
import { HowItWorks } from '@/Components/Landing/HowItWorks';
import { Footer } from '@/Components/Landing/Footer';

export default function Landing() {
    return (
        <>
            <Head title="NF Facilitator — Emissão de NF-e para afiliados Shopee">
                <meta
                    name="description"
                    content="Automatize a emissão de notas fiscais para afiliados Shopee. Upload do relatório, validação automática e geração em lote."
                />
            </Head>

            <div className="bg-white font-sans text-neutral-900 antialiased">
                <Header />
                <Hero />
                <HowItWorks />
                <Footer />
            </div>
        </>
    );
}
