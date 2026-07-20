import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { FileText } from 'lucide-react';
import { Button } from '@/Components/ui/Button';
import { EmptyState } from '@/Components/EmptyState';
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
    if (invoices.data.length === 0) {
        return (
            <AppLayout header={<h2 className="text-base font-semibold text-foreground">Notas</h2>}>
                <Head title="Notas" />
                <EmptyState
                    icon={FileText}
                    title="Nenhuma nota ainda"
                    description="As notas são geradas a partir de uma importação. Envie um relatório de comissões e depois gere as notas."
                    action={
                        <Button asChild>
                            <Link href={route('imports.create')}>Importar relatório</Link>
                        </Button>
                    }
                />
            </AppLayout>
        );
    }

    return (
        <AppLayout header={<h2 className="text-base font-semibold text-foreground">Notas</h2>}>
            <Head title="Notas" />

            <div className="space-y-4">
                <div className="space-y-4">
                    <div className="rounded-md border bg-white">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Afiliado</TableHead>
                                    <TableHead>Competência</TableHead>
                                    <TableHead>Valor</TableHead>
                                    <TableHead>Nº da nota</TableHead>
                                    <TableHead>Status</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
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
