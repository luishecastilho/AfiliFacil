import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { Pagination } from '@/Components/Pagination';
import { formatTaxDocument } from '@/lib/formatters';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/Table';

export default function Index({ sellers }) {
    return (
        <AppLayout header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Sellers</h2>}>
            <Head title="Sellers" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-4 sm:px-6 lg:px-8">
                    <div className="rounded-md border bg-white">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Document</TableHead>
                                    <TableHead>Email</TableHead>
                                    <TableHead>City / State</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {sellers.data.length === 0 && (
                                    <TableRow>
                                        <TableCell colSpan={4} className="h-24 text-center">
                                            No sellers yet.
                                        </TableCell>
                                    </TableRow>
                                )}
                                {sellers.data.map((seller) => (
                                    <TableRow key={seller.id}>
                                        <TableCell>
                                            <Link href={route('sellers.edit', seller.id)} className="font-medium hover:underline">
                                                {seller.name}
                                            </Link>
                                        </TableCell>
                                        <TableCell>{formatTaxDocument(seller.tax_document)}</TableCell>
                                        <TableCell>{seller.email ?? '—'}</TableCell>
                                        <TableCell>
                                            {seller.address_city ? `${seller.address_city} / ${seller.address_state}` : '—'}
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </div>

                    <Pagination links={sellers.links} />
                </div>
            </div>
        </AppLayout>
    );
}
