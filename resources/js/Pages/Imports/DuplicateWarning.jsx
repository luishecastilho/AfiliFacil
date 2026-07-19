import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { Button } from '@/Components/ui/Button';

export default function DuplicateWarning({ existingImportId }) {
    const { post, processing } = useForm();

    function confirmOverride() {
        post(route('imports.store', { override: true }));
    }

    return (
        <AppLayout header={<h2 className="text-xl font-semibold leading-tight text-gray-800">Duplicate Import</h2>}>
            <Head title="Duplicate Import" />

            <div className="py-12">
                <div className="mx-auto max-w-xl space-y-4 rounded-md border bg-white p-6 sm:px-6 lg:px-8">
                    <p>
                        This file was already imported as{' '}
                        <Link href={route('imports.show', existingImportId)} className="underline">
                            import #{existingImportId}
                        </Link>
                        .
                    </p>
                    <p className="text-sm text-muted-foreground">
                        You can view the existing import, or confirm to upload it again as a new import.
                    </p>
                    <div className="flex gap-2">
                        <Button variant="outline" asChild>
                            <Link href={route('imports.show', existingImportId)}>View Existing Import</Link>
                        </Button>
                        <Button onClick={confirmOverride} disabled={processing}>
                            Upload Anyway
                        </Button>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
}
