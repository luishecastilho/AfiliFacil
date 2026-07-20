import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { ShieldAlert } from 'lucide-react';
import { Button } from '@/Components/ui/Button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/Card';
import { formatCurrency } from '@/lib/formatters';

export default function Index({ plans, currentPlan, nfUsedThisMonth, nfLimit, hasStripeSubscription }) {
    const checkoutForm = useForm({ plan: '' });
    const portalForm = useForm({});
    const fiscal = usePage().props.fiscal;
    const fiscalReady = fiscal?.complete ?? false;

    function subscribe(plan) {
        checkoutForm.setData('plan', plan);
        checkoutForm.post(route('billing.checkout'));
    }

    function manageSubscription() {
        portalForm.post(route('billing.portal'));
    }

    return (
        <AppLayout header={<h2 className="text-base font-semibold text-foreground">Assinatura</h2>}>
            <Head title="Assinatura" />

            <div className="space-y-4">
                <div className="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
                    {!fiscalReady && (
                        <Card className="border-amber-400 bg-amber-50 dark:bg-amber-950/20">
                            <CardContent className="flex items-start gap-3 p-4">
                                <ShieldAlert className="mt-0.5 size-5 shrink-0 text-amber-600" />
                                <div className="space-y-2 text-sm">
                                    <p className="font-medium text-foreground">
                                        Complete seu cadastro fiscal antes de assinar
                                    </p>
                                    <p className="text-muted-foreground">
                                        A emissão de NFS-e exige um emitente configurado e validado com o portal nacional.
                                    </p>
                                    <Button asChild size="sm" variant="outline">
                                        <Link href={route('issuer.edit')}>Completar cadastro fiscal</Link>
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    )}

                    <Card>
                        <CardHeader>
                            <CardTitle>Plano atual: {plans[currentPlan]?.name}</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <p className="text-sm text-muted-foreground">
                                {nfLimit === null
                                    ? `${nfUsedThisMonth} NF-e emitidas este mês (ilimitado)`
                                    : `${nfUsedThisMonth} de ${nfLimit} NF-e usadas este mês`}
                            </p>

                            {hasStripeSubscription && (
                                <Button variant="outline" onClick={manageSubscription} disabled={portalForm.processing}>
                                    Gerenciar assinatura
                                </Button>
                            )}
                        </CardContent>
                    </Card>

                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-3">
                        {Object.entries(plans).map(([key, plan]) => (
                            <Card key={key} className={key === currentPlan ? 'border-[#EE4D2D]' : ''}>
                                <CardHeader>
                                    <CardTitle className="text-base">{plan.name}</CardTitle>
                                </CardHeader>
                                <CardContent className="space-y-3">
                                    <p className="text-2xl font-bold">
                                        {plan.price > 0 ? `${formatCurrency(plan.price)}/mês` : 'Grátis'}
                                    </p>
                                    <p className="text-sm text-muted-foreground">
                                        {plan.nf_limit === null ? 'NF-e ilimitadas' : `${plan.nf_limit} NF-e por mês`}
                                    </p>
                                    {key !== currentPlan && key !== 'free' && (
                                        <Button
                                            className="w-full"
                                            onClick={() => subscribe(key)}
                                            disabled={checkoutForm.processing || !fiscalReady}
                                            title={!fiscalReady ? 'Complete seu cadastro fiscal para assinar' : undefined}
                                        >
                                            Assinar agora
                                        </Button>
                                    )}
                                </CardContent>
                            </Card>
                        ))}
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
