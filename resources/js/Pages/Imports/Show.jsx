import AppLayout from '@/Layouts/AppLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Button } from '@/Components/ui/Button';
import { SummaryCard } from '@/Components/SummaryCard';
import { StatusBadge } from '@/Components/StatusBadge';
import { Pagination } from '@/Components/Pagination';
import { ProgressBar } from '@/Components/ProgressBar';
import { IMPORT_ROW_STATUS_LABELS, IMPORT_STATUS_LABELS } from '@/constants/statuses';
import { formatCurrency, formatReferenceMonth } from '@/lib/formatters';
import { useImportPoller } from '@/hooks/useImportPoller';
import { useInvoicePoller } from '@/hooks/useInvoicePoller';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/Table';

const TERMINAL_IMPORT_STATUSES = ['validated', 'done', 'failed', 'cancelled'];

export default function Show({ import: importRecord, rows }) {
    const [showInvalidWarning] = useState(importRecord.invalid_rows > 0);

    useImportPoller(importRecord.id, importRecord.status);

    const progress = useInvoicePoller(
        route('invoices.progress', importRecord.id),
        TERMINAL_IMPORT_STATUSES.includes(importRecord.status),
    );

    const { post, processing } = useForm();

    function generateInvoices() {
        post(route('invoices.generate', importRecord.id));
    }

    const totalInvoices = Object.values(progress).reduce((sum, count) => sum + count, 0);
    const generatedInvoices = progress.generated ?? 0;

    return (
        <AppLayout
            header={
                <h2 className="text-base font-semibold text-foreground">
                    Import: {importRecord.original_filename}
                </h2>
            }
        >
            <Head title={`Import #${importRecord.id}`} />

            <div className="space-y-6">
                <div className="space-y-6">
                    <div className="flex items-center justify-between rounded-md border bg-white p-4">
                        <StatusBadge status={importRecord.status} label={IMPORT_STATUS_LABELS[importRecord.status]} />

                        {importRecord.status === 'validated' && (
                            <Button onClick={generateInvoices} disabled={processing}>
                                Generate All Invoices
                            </Button>
                        )}

                        {totalInvoices > 0 && (
                            <a href={route('imports.download.zip', importRecord.id)} className="text-sm underline">
                                Download All as ZIP
                            </a>
                        )}
                    </div>

                    {totalInvoices > 0 && (
                        <div className="space-y-2 rounded-md border bg-white p-4">
                            <p className="text-sm text-muted-foreground">
                                {generatedInvoices} of {totalInvoices} invoices generated
                            </p>
                            <ProgressBar value={generatedInvoices} max={totalInvoices} />
                        </div>
                    )}

                    {showInvalidWarning && (
                        <div className="rounded-md border border-yellow-300 bg-yellow-50 p-4 text-sm text-yellow-800">
                            This import has {importRecord.invalid_rows} invalid row(s). They will be skipped during
                            invoice generation unless corrected.
                        </div>
                    )}

                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                        <SummaryCard title="Total Rows" value={importRecord.total_rows ?? '—'} />
                        <SummaryCard title="Valid" value={importRecord.valid_rows ?? '—'} />
                        <SummaryCard title="Invalid" value={importRecord.invalid_rows ?? '—'} />
                        <SummaryCard title="Duplicate" value={importRecord.duplicate_rows ?? '—'} />
                        <SummaryCard title="Total Commission" value={formatCurrency(importRecord.total_amount)} />
                        <SummaryCard title="Unique Sellers" value={importRecord.total_unique_tax_ids ?? '—'} />
                    </div>

                    {rows && (
                        <div className="rounded-md border bg-white">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Seller</TableHead>
                                        <TableHead>Document</TableHead>
                                        <TableHead>Amount</TableHead>
                                        <TableHead>Reference Month</TableHead>
                                        <TableHead>Status</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {rows.data.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>{row.seller_name}</TableCell>
                                            <TableCell>{row.seller_document}</TableCell>
                                            <TableCell>{formatCurrency(row.invoice_amount)}</TableCell>
                                            <TableCell>{formatReferenceMonth(row.reference_month)}</TableCell>
                                            <TableCell>
                                                <StatusBadge status={row.status} label={IMPORT_ROW_STATUS_LABELS[row.status]} />
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                            <Pagination links={rows.links} />
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
