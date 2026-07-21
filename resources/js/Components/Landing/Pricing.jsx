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

    return (
        <div
            className={`relative flex flex-col rounded-2xl border bg-white p-8 ${
                plan.highlighted ? 'border-2 border-[#EE4D2D] shadow-xl shadow-[#EE4D2D]/10' : 'border-neutral-200'
            }`}
        >
            {plan.highlighted && (
                <span className="absolute -top-3 left-1/2 -translate-x-1/2 rounded-full bg-[#EE4D2D] px-3 py-1 text-xs font-semibold text-white">
                    Mais popular
                </span>
            )}

            <h3 className="text-lg font-semibold text-neutral-900">{plan.name}</h3>

            <p className="mt-4 flex items-baseline gap-1">
                <span className="text-4xl font-extrabold tracking-tight text-neutral-900">
                    {plan.price === 0 ? 'Grátis' : `R$ ${formatPrice(plan.price)}`}
                </span>
                {plan.price > 0 && <span className="text-sm font-medium text-neutral-500">/mês</span>}
            </p>

            <p className="mt-2 text-sm text-neutral-500">{plan.limit}</p>

            <ul className="mt-6 flex-1 space-y-3">
                {plan.features.map((feature) => (
                    <li key={feature} className="flex items-start gap-2 text-sm text-neutral-600">
                        <Check className="mt-0.5 h-4 w-4 shrink-0 text-[#EE4D2D]" />
                        {feature}
                    </li>
                ))}
            </ul>

            {plan.key === 'free' ? (
                <Link
                    href={route('register')}
                    className="mt-8 block w-full rounded-full border border-neutral-300 px-6 py-3 text-center text-sm font-semibold text-neutral-900 transition hover:bg-neutral-50"
                >
                    Começar grátis
                </Link>
            ) : (
                <form onSubmit={subscribe}>
                    <button
                        type="submit"
                        disabled={processing}
                        className="mt-8 w-full rounded-full bg-[#EE4D2D] px-6 py-3 text-sm font-semibold text-white transition hover:bg-[#D94426] disabled:opacity-60"
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
        <section id="planos" className="bg-neutral-50 py-24">
            <div className="mx-auto max-w-7xl px-6 lg:px-8">
                <div className="mx-auto max-w-2xl text-center">
                    <h2 className="text-3xl font-bold tracking-tight text-neutral-900 sm:text-4xl">
                        Planos para todo tamanho de afiliado
                    </h2>
                    <p className="mt-4 text-lg text-neutral-500">
                        Comece de graça e mude de plano quando suas comissões crescerem.
                    </p>
                </div>

                <div className="mt-16 grid grid-cols-1 gap-8 sm:grid-cols-3">
                    {PLANS.map((plan) => (
                        <PricingCard key={plan.key} plan={plan} />
                    ))}
                </div>
            </div>
        </section>
    );
}
