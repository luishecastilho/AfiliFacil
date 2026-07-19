import AppLayout from '@/Layouts/AppLayout';
import { Head, Link } from '@inertiajs/react';
import { SummaryCard } from '@/Components/SummaryCard';
import { Button } from '@/Components/ui/Button';

export default function Index({ summary }) {
    return (
        <AppLayout header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Dashboard</h2>}>
            <Head title="Dashboard" />

            <div className="py-12">
                <div className="mx-auto max-w-7xl space-y-6 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        <SummaryCard title="Total Imports" value={summary?.total_imports ?? 0} />
                        <SummaryCard title="Total Invoices" value={summary?.total_invoices ?? 0} />
                    </div>

                    {summary?.total_imports === 0 && (
                        <div className="flex flex-col items-center gap-4 rounded-lg border border-dashed p-12 text-center">
                            <p className="text-muted-foreground">You haven't created any imports yet.</p>
                            <Button asChild>
                                <Link href={route('imports.create')}>Create your first import</Link>
                            </Button>
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
