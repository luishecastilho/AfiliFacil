import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { CreditCard, FileText, Receipt, Upload, Users } from 'lucide-react';
import { OnboardingChecklist } from '@/Components/App/OnboardingChecklist';
import { UpgradePrompt } from '@/Components/App/UpgradePrompt';
import { Button } from '@/Components/ui/Button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/Card';
import { useFreeTierStatus } from '@/hooks/useFreeTierStatus';

function StatCard({ title, value, hint, icon: Icon }) {
    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0 pb-2">
                <CardTitle className="text-sm font-medium text-muted-foreground">{title}</CardTitle>
                <Icon className="size-4 text-muted-foreground" />
            </CardHeader>
            <CardContent>
                <div className="text-2xl font-bold tabular-nums">{value}</div>
                {hint && <p className="mt-1 text-xs text-muted-foreground">{hint}</p>}
            </CardContent>
        </Card>
    );
}

export default function Index({ summary }) {
    const { nf_usage: usage, fiscal } = usePage().props;
    const freeTier = useFreeTierStatus();

    const usageLabel = usage
        ? usage.limit === null
            ? `${usage.used} emitidas`
            : `${usage.used} de ${usage.limit}`
        : '—';

    // Mostra o guia enquanto o cadastro não estiver pronto ou não houver nenhuma nota.
    const showOnboarding = !fiscal?.complete || (summary?.total_invoices ?? 0) === 0;

    return (
        <AppLayout header={<h2 className="text-base font-semibold text-foreground">Início</h2>}>
            <Head title="Início" />

            {showOnboarding && <OnboardingChecklist summary={summary} />}

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title="Importações"
                    value={summary?.total_imports ?? 0}
                    hint="Relatórios de comissão enviados"
                    icon={Upload}
                />
                <StatCard
                    title="Notas emitidas"
                    value={summary?.total_invoices ?? 0}
                    hint="NFS-e geradas no total"
                    icon={FileText}
                />
                <StatCard
                    title="Emitidas este mês"
                    value={summary?.issued_this_month ?? 0}
                    hint={`Uso do plano: ${usageLabel}`}
                    icon={Receipt}
                />
                <StatCard
                    title="Afiliados"
                    value={summary?.total_sellers ?? 0}
                    hint="Tomadores cadastrados"
                    icon={Users}
                />
            </div>

            <Card>
                <CardHeader>
                    <CardTitle className="text-base">Ações rápidas</CardTitle>
                </CardHeader>
                <CardContent className="flex flex-wrap gap-3">
                    <Button asChild>
                        <Link href={route('imports.create')}>
                            <Upload className="mr-2 size-4" />
                            Nova importação
                        </Link>
                    </Button>
                    <Button asChild variant="outline">
                        <Link href={route('invoices.index')}>
                            <FileText className="mr-2 size-4" />
                            Ver notas
                        </Link>
                    </Button>
                    <Button asChild variant="outline">
                        <Link href={route('billing.index')}>
                            <CreditCard className="mr-2 size-4" />
                            Gerenciar plano
                        </Link>
                    </Button>
                </CardContent>
            </Card>

            {freeTier.isFree && (
                <UpgradePrompt
                    variant={freeTier.atLimit ? 'warning' : 'info'}
                    title="Você está no plano Gratuito"
                >
                    {freeTier.atLimit
                        ? `Você já usou as ${freeTier.limit} notas grátis deste mês. Faça upgrade para continuar emitindo agora.`
                        : `Seu plano inclui ${freeTier.limit} notas por mês (${freeTier.used} usadas). Faça upgrade para emitir muito mais e desbloquear planos maiores.`}
                </UpgradePrompt>
            )}
        </AppLayout>
    );
}
