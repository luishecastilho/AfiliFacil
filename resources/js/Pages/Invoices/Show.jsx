import AppLayout from '@/Layouts/AppLayout';
import { Head, useForm } from '@inertiajs/react';
import { Alert } from '@/Components/ui/Alert';
import { Button } from '@/Components/ui/Button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/Card';
import { StatusBadge } from '@/Components/StatusBadge';
import { InvoiceTimeline } from '@/Components/InvoiceTimeline';
import { INVOICE_STATUS_LABELS } from '@/constants/statuses';
import { formatCurrency, formatReferenceMonth } from '@/lib/formatters';

export default function Show({ invoice }) {
    const { post, processing } = useForm();

    function retry() {
        post(route('invoices.retry', invoice.id));
    }

    const hasPdf = invoice.files?.some((file) => file.type === 'pdf');
    const hasXml = invoice.files?.some((file) => file.type === 'xml');

    return (
        <AppLayout
            header={<h2 className="text-base font-semibold text-foreground">Nota #{invoice.id}</h2>}
        >
            <Head title={`Nota #${invoice.id}`} />

            <div className="space-y-4">
                <div className="mx-auto grid max-w-5xl grid-cols-1 gap-6 sm:px-6 lg:grid-cols-3 lg:px-8">
                    <div className="space-y-4 lg:col-span-2">
                        {invoice.status === 'failed' && (
                            <Alert
                                variant="destructive"
                                title="A emissão desta nota falhou"
                                action={
                                    <Button size="sm" onClick={retry} disabled={processing}>
                                        Tentar novamente
                                    </Button>
                                }
                            >
                                Veja o motivo no histórico ao lado. Se for um dado incorreto (ex.: CNPJ do afiliado),
                                corrija no relatório e importe de novo; caso contrário, tente novamente.
                            </Alert>
                        )}

                        <Card>
                            <CardHeader>
                                <CardTitle className="flex items-center justify-between">
                                    <span>{invoice.seller?.name}</span>
                                    <StatusBadge status={invoice.status} label={INVOICE_STATUS_LABELS[invoice.status]} />
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="space-y-2 text-sm">
                                <p>Competência: {formatReferenceMonth(invoice.reference_month)}</p>
                                <p>Valor: {formatCurrency(invoice.amount)}</p>
                                <p>Número da nota: {invoice.invoice_number ?? '—'}</p>
                                <p className="break-all">Chave de acesso: {invoice.access_key ?? '—'}</p>

                                {(hasPdf || hasXml) && (
                                    <p className="pt-2 text-xs text-muted-foreground">
                                        O <strong>PDF</strong> (DANFSE) é a versão para leitura/impressão; o{' '}
                                        <strong>XML</strong> é o documento fiscal oficial — guarde-o por 5 anos.
                                    </p>
                                )}

                                <div className="flex gap-2 pt-4">
                                    {hasPdf && (
                                        <Button variant="outline" asChild>
                                            <a href={route('invoices.download', [invoice.id, 'pdf'])}>Baixar PDF</a>
                                        </Button>
                                    )}
                                    {hasXml && (
                                        <Button variant="outline" asChild>
                                            <a href={route('invoices.download', [invoice.id, 'xml'])}>Baixar XML</a>
                                        </Button>
                                    )}
                                </div>
                            </CardContent>
                        </Card>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle>Histórico</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <InvoiceTimeline events={invoice.events} />
                        </CardContent>
                    </Card>
                </div>
            </div>
        </AppLayout>
    );
}
