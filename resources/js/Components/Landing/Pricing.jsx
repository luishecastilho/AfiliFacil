import { Link, useForm } from '@inertiajs/react';
import { Check } from 'lucide-react';

const PLANS = [
    {
        key: 'free',
        name: 'Gratuito',
        price: 0,
        limit: '5 NFS-e por mês',
        features: ['5 NFS-e por mês', 'Upload de relatório Shopee', 'Download individual'],
        highlighted: false,
    },
    {
        key: 'basic',
        name: 'Básico',
        price: 39.9,
        limit: '50 NFS-e por mês',
        features: ['50 NFS-e por mês', 'Tudo do Gratuito', 'Download em ZIP', 'Suporte por email'],
        highlighted: true,
    },
    {
        key: 'advanced',
        name: 'Avançado',
        price: 169.9,
        limit: 'NFS-e ilimitadas',
        features: ['NFS-e ilimitadas', 'Tudo do Básico', 'Processamento prioritário', 'Suporte prioritário'],
        highlighted: false,
    },
];

function formatPrice(price) {
    return price.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

function PricingCard({ plan }) {
    const { post, processing } = useForm({ plan: plan.key });

    function subscribe(event) {
        event.preventDefault();
        post(route('billing.checkout'));
    }

    const dark = plan.highlighted;

    return (
        <div
            className={`relative flex flex-col rounded-3xl p-8 ${
                dark
                    ? 'bg-[#0a0a0a] text-white shadow-2xl shadow-[#EE4D2D]/15 lg:scale-105'
                    : 'border border-neutral-200 bg-white'
            }`}
        >
            {dark && (
                <span className="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-[#EE4D2D] px-4 py-1 text-xs font-semibold text-white">
                    Mais popular
                </span>
            )}

            <h3 className={`font-display text-lg font-bold ${dark ? 'text-white' : 'text-neutral-900'}`}>{plan.name}</h3>

            <p className="mt-4 flex items-baseline gap-1">
                <span
                    className={`font-display text-5xl font-bold tracking-tight ${
                        dark ? 'text-[#EE4D2D]' : 'text-neutral-900'
                    }`}
                >
                    {plan.price === 0 ? 'Grátis' : `R$ ${formatPrice(plan.price)}`}
                </span>
                {plan.price > 0 && (
                    <span className={`text-sm font-medium ${dark ? 'text-neutral-400' : 'text-neutral-500'}`}>/mês</span>
                )}
            </p>

            <p className={`mt-2 text-sm ${dark ? 'text-neutral-400' : 'text-neutral-500'}`}>{plan.limit}</p>

            <ul className="mt-6 flex-1 space-y-3">
                {plan.features.map((feature) => (
                    <li
                        key={feature}
                        className={`flex items-start gap-2 text-sm ${dark ? 'text-neutral-300' : 'text-neutral-600'}`}
                    >
                        <Check className="mt-0.5 h-4 w-4 shrink-0 text-[#EE4D2D]" />
                        {feature}
                    </li>
                ))}
            </ul>

            {plan.key === 'free' ? (
                <Link
                    href={route('register')}
                    className="mt-8 block w-full rounded-full border border-neutral-300 px-6 py-3.5 text-center text-sm font-semibold text-neutral-900 transition hover:bg-neutral-50"
                >
                    Começar grátis
                </Link>
            ) : (
                <form onSubmit={subscribe}>
                    <button
                        type="submit"
                        disabled={processing}
                        className={`mt-8 w-full rounded-full px-6 py-3.5 text-sm font-semibold transition disabled:opacity-60 ${
                            dark
                                ? 'bg-[#EE4D2D] text-white hover:scale-[1.02] hover:bg-[#D94426]'
                                : 'bg-neutral-900 text-white hover:bg-neutral-800'
                        }`}
                    >
                        Assinar agora
                    </button>
                </form>
            )}
        </div>
    );
}

export function Pricing() {
    return (
        <section id="planos" className="bg-neutral-50 py-24 lg:py-32">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="mx-auto max-w-2xl text-center">
                    <h2 className="font-display text-4xl font-bold tracking-tight text-neutral-900 sm:text-5xl">
                        Planos para todo tamanho de{' '}
                        <em className="font-serif font-normal italic text-[#EE4D2D]">afiliado</em>
                    </h2>
                    <p className="mt-4 text-lg text-neutral-500">
                        Comece de graça e mude de plano quando suas comissões crescerem.
                    </p>
                </div>

                <div className="mt-20 grid grid-cols-1 gap-8 sm:grid-cols-3 lg:gap-6">
                    {PLANS.map((plan) => (
                        <PricingCard key={plan.key} plan={plan} />
                    ))}
                </div>
            </div>
        </section>
    );
}
