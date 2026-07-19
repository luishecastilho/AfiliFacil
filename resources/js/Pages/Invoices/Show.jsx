import AppLayout from '@/Layouts/AppLayout';
import { Head, useForm } from '@inertiajs/react';
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

    return (
        <AppLayout
            header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Invoice #{invoice.id}</h2>}
        >
            <Head title={`Invoice #${invoice.id}`} />

            <div className="py-12">
                <div className="mx-auto grid max-w-5xl grid-cols-1 gap-6 sm:px-6 lg:grid-cols-3 lg:px-8">
                    <Card className="lg:col-span-2">
                        <CardHeader>
                            <CardTitle className="flex items-center justify-between">
                                <span>{invoice.seller?.name}</span>
                                <StatusBadge status={invoice.status} label={INVOICE_STATUS_LABELS[invoice.status]} />
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-2 text-sm">
                            <p>Reference month: {formatReferenceMonth(invoice.reference_month)}</p>
                            <p>Amount: {formatCurrency(invoice.amount)}</p>
                            <p>Invoice number: {invoice.invoice_number ?? '—'}</p>
                            <p>Access key: {invoice.access_key ?? '—'}</p>

                            <div className="flex gap-2 pt-4">
                                {invoice.files?.some((file) => file.type === 'pdf') && (
                                    <Button variant="outline" asChild>
                                        <a href={route('invoices.download', [invoice.id, 'pdf'])}>Download PDF</a>
                                    </Button>
                                )}
                                {invoice.files?.some((file) => file.type === 'xml') && (
                                    <Button variant="outline" asChild>
                                        <a href={route('invoices.download', [invoice.id, 'xml'])}>Download XML</a>
                                    </Button>
                                )}
                                {invoice.status === 'failed' && (
                                    <Button onClick={retry} disabled={processing}>
                                        Retry
                                    </Button>
                                )}
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Timeline</CardTitle>
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
