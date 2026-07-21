import { Head } from '@inertiajs/react';
import { Header } from '@/Components/Landing/Header';
import { Hero } from '@/Components/Landing/Hero';
import { Problem } from '@/Components/Landing/Problem';
import { HowItWorks } from '@/Components/Landing/HowItWorks';
import { Features } from '@/Components/Landing/Features';
import { Pricing } from '@/Components/Landing/Pricing';
import { Faq, FAQ_ITEMS } from '@/Components/Landing/Faq';
import { FinalCta } from '@/Components/Landing/FinalCta';
import { Footer } from '@/Components/Landing/Footer';

const META_TITLE = 'Nota Fiscal de Afiliado Shopee Automática — AfiliFacil';
const META_DESCRIPTION =
    'Emita todas as NFS-e das suas comissões Shopee de uma vez: importe o relatório, valide e gere as notas em 1 clique. Rápido, fácil e automático. Comece grátis.';

const FAQ_SCHEMA = {
    '@context': 'https://schema.org',
    '@type': 'FAQPage',
    mainEntity: FAQ_ITEMS.map((item) => ({
        '@type': 'Question',
        name: item.question,
        acceptedAnswer: { '@type': 'Answer', text: item.answer },
    })),
};

export default function Landing() {
    return (
        <>
            <Head title={META_TITLE}>
                <meta name="description" content={META_DESCRIPTION} />
                <meta property="og:title" content={META_TITLE} />
                <meta property="og:description" content={META_DESCRIPTION} />
                <meta property="og:type" content="website" />
                <meta property="og:locale" content="pt_BR" />
                <meta name="twitter:card" content="summary" />
                <meta name="twitter:title" content={META_TITLE} />
                <meta name="twitter:description" content={META_DESCRIPTION} />
                <link rel="canonical" href="https://afilifacil.com.br" />
                <meta property="og:url" content="https://afilifacil.com.br" />
                {/* NEEDS INPUT: og:image (imagem de compartilhamento social) */}
                <script
                    type="application/ld+json"
                    dangerouslySetInnerHTML={{ __html: JSON.stringify(FAQ_SCHEMA) }}
                />
            </Head>

            <div className="bg-white font-sans text-neutral-900 antialiased">
                <Header />
                <Hero />
                <Problem />
                <HowItWorks />
                <Features />
                <Pricing />
                <Faq />
                <FinalCta />
                <Footer />
            </div>
        </>
    );
}
