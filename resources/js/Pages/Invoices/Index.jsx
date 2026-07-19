import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { StatusBadge } from '@/Components/StatusBadge';
import { Pagination } from '@/Components/Pagination';
import { INVOICE_STATUS_LABELS } from '@/constants/statuses';
import { formatCurrency, formatReferenceMonth } from '@/lib/formatters';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/Table';

export default function Index({ invoices }) {
    return (
        <AppLayout header={<h2 className="text-base font-semibold text-foreground">Invoices</h2>}>
            <Head title="Invoices" />

            <div className="space-y-4">
                <div className="space-y-4">
                    <div className="rounded-md border bg-white">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Seller</TableHead>
                                    <TableHead>Reference Month</TableHead>
                                    <TableHead>Amount</TableHead>
                                    <TableHead>Invoice #</TableHead>
                                    <TableHead>Status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {invoices.data.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={5} className="h-24 text-center">
                                            No invoices yet.
                                        </TableCell>
                                    </TableRow>
                                )}
                                {invoices.data.map((invoice) => (
                                    <TableRow key={invoice.id}>
                                        <TableCell>
                                            <Link href={route('invoices.show', invoice.id)} className="font-medium hover:underline">
                                                {invoice.seller?.name}
                                            </Link>
                                        </TableCell>
                                        <TableCell>{formatReferenceMonth(invoice.reference_month)}</TableCell>
                                        <TableCell>{formatCurrency(invoice.amount)}</TableCell>
                                        <TableCell>{invoice.invoice_number ?? '—'}</TableCell>
                                        <TableCell>
                                            <StatusBadge status={invoice.status} label={INVOICE_STATUS_LABELS[invoice.status]} />
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>

                    <Pagination links={invoices.links} />
                </div>
            </div>
        </AppLayout>
    );
}
