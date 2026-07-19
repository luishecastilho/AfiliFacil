import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import { CreditCard, FileText, Receipt, Upload, Users } from 'lucide-react';
import { Button } from '@/Components/ui/Button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/Card';

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
    const { nf_usage: usage } = usePage().props;

    const usageLabel = usage
        ? usage.limit === null
            ? `${usage.used} emitidas`
            : `${usage.used} de ${usage.limit}`
        : '—';

    return (
        <AppLayout header={<h2 className="text-base font-semibold text-foreground">Dashboard</h2>}>
            <Head title="Dashboard" />

            <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    title="Total Imports"
                    value={summary?.total_imports ?? 0}
                    hint="Commission reports uploaded"
                    icon={Upload}
                />
                <StatCard
                    title="Total Invoices"
                    value={summary?.total_invoices ?? 0}
                    hint="NF-e generated all-time"
                    icon={FileText}
                />
                <StatCard
                    title="Issued This Month"
                    value={summary?.issued_this_month ?? 0}
                    hint={`Plan usage: ${usageLabel}`}
                    icon={Receipt}
                />
                <StatCard
                    title="Sellers"
                    value={summary?.total_sellers ?? 0}
                    hint="Registered affiliates"
                    icon={Users}
                />
            </div>

            {summary?.total_imports === 0 ? (
                <Card className="border-dashed">
                    <CardContent className="flex flex-col items-center gap-4 py-12 text-center">
                        <div className="flex size-12 items-center justify-center rounded-full bg-muted">
                            <Upload className="size-6 text-muted-foreground" />
                        </div>
                        <div className="space-y-1">
                            <p className="font-medium">You haven't created any imports yet</p>
                            <p className="text-sm text-muted-foreground">
                                Upload a Shopee commission report to generate your first invoices.
                            </p>
                        </div>
                        <Button asChild>
                            <Link href={route('imports.create')}>Create your first import</Link>
                        </Button>
                    </CardContent>
                </Card>
            ) : (
                <Card>
                    <CardHeader>
                        <CardTitle className="text-base">Quick actions</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-wrap gap-3">
                        <Button asChild>
                            <Link href={route('imports.create')}>
                                <Upload className="mr-2 size-4" />
                                New import
                            </Link>
                        </Button>
                        <Button asChild variant="outline">
                            <Link href={route('invoices.index')}>
                                <FileText className="mr-2 size-4" />
                                View invoices
                            </Link>
                        </Button>
                        <Button asChild variant="outline">
                            <Link href={route('billing.index')}>
                                <CreditCard className="mr-2 size-4" />
                                Manage plan
                            </Link>
                        </Button>
                    </CardContent>
                </Card>
            )}
        </AppLayout>
    );
}
