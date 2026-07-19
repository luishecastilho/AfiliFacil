import AppLayout from '@/Layouts/AppLayout';
import { Head, useForm } from '@inertiajs/react';
import { FileUploadZone } from '@/Components/FileUploadZone';
import { Button } from '@/Components/ui/Button';
import { Label } from '@/Components/ui/Label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/Select';

export default function Create({ marketplaces }) {
    const { data, setData, post, processing, errors } = useForm({
        marketplace_id: marketplaces[0]?.id ?? '',
        file: null,
    });

    function submit(event) {
        event.preventDefault();
        post(route('imports.store'), { forceFormData: true });
    }

    return (
        <AppLayout header={<h2 className="text-xl font-semibold leading-tight text-gray-800">New Import</h2>}>
            <Head title="New Import" />

            <div className="py-12">
                <div className="mx-auto max-w-2xl sm:px-6 lg:px-8">
                    <form onSubmit={submit} className="space-y-6 rounded-md border bg-white p-6">
                        <div className="space-y-2">
                            <Label htmlFor="marketplace_id">Marketplace</Label>
                            <Select value={String(data.marketplace_id)} onValueChange={(value) => setData('marketplace_id', value)}>
                                <SelectTrigger id="marketplace_id">
                                    <SelectValue placeholder="Select a marketplace" />
                                </SelectTrigger>
                                <SelectContent>
                                    {marketplaces.map((marketplace) => (
                                        <SelectItem key={marketplace.id} value={String(marketplace.id)}>
                                            {marketplace.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.marketplace_id && <p className="text-sm text-destructive">{errors.marketplace_id}</p>}
                        </div>

                        <div className="space-y-2">
                            <Label>Commission Report</Label>
                            <FileUploadZone file={data.file} onFileSelect={(file) => setData('file', file)} />
                            {errors.file && <p className="text-sm text-destructive">{errors.file}</p>}
                        </div>

                        <Button type="submit" disabled={processing || !data.file}>
                            Upload &amp; Parse
                        </Button>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
