import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { Upload } from 'lucide-react';
import { Button } from '@/Components/ui/Button';
import { EmptyState } from '@/Components/EmptyState';
import { StatusBadge } from '@/Components/StatusBadge';
import { Pagination } from '@/Components/Pagination';
import { IMPORT_STATUS_LABELS } from '@/constants/statuses';
import { formatCurrency, formatDate } from '@/lib/formatters';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/Table';

export default function Index({ imports }) {
    if (imports.data.length === 0) {
        return (
            <AppLayout header={<h2 className="text-base font-semibold text-foreground">Importações</h2>}>
                <Head title="Importações" />
                <EmptyState
                    icon={Upload}
                    title="Nenhuma importação ainda"
                    description="Envie o relatório de comissões da Shopee para a plataforma gerar suas notas fiscais automaticamente."
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
        <AppLayout header={<h2 className="text-base font-semibold text-foreground">Importações</h2>}>
            <Head title="Importações" />

            <div className="space-y-4">
                <div className="space-y-4">
                    <div className="flex justify-end">
                        <Button asChild>
                            <Link href={route('imports.create')}>Nova importação</Link>
                        </Button>
                    </div>

                    <div className="rounded-md border bg-white">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Arquivo</TableHead>
                                    <TableHead>Marketplace</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Valor total</TableHead>
                                    <TableHead>Criado em</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {imports.data.map((item) => (
                                    <TableRow key={item.id}>
                                        <TableCell>
                                            <Link href={route('imports.show', item.id)} className="font-medium hover:underline">
                                                {item.original_filename}
                                            </Link>
                                        </TableCell>
                                        <TableCell>{item.marketplace?.name}</TableCell>
                                        <TableCell>
                                            <StatusBadge status={item.status} label={IMPORT_STATUS_LABELS[item.status]} />
                                        </TableCell>
                                        <TableCell>{formatCurrency(item.total_amount)}</TableCell>
                                        <TableCell>{formatDate(item.created_at)}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>

                    <Pagination links={imports.links} />
                </div>
            </div>
        </AppLayout>
    );
}
