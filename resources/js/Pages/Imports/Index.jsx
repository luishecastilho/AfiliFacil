import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { Button } from '@/Components/ui/Button';
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
    return (
        <AppLayout header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Imports</h2>}>
            <Head title="Imports" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
                    <div className="flex justify-end">
                        <Button asChild>
                            <Link href={route('imports.create')}>New Import</Link>
                        </Button>
                    </div>

                    <div className="rounded-md border bg-white">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>File</TableHead>
                                    <TableHead>Marketplace</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Total Amount</TableHead>
                                    <TableHead>Created</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {imports.data.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={5} className="h-24 text-center">
                                            No imports yet.
                                        </TableCell>
                                    </TableRow>
                                )}
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
