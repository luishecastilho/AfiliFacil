import AppLayout from '@/Layouts/AppLayout';
import { Head, useForm } from '@inertiajs/react';
import { Button } from '@/Components/ui/Button';
import { Input } from '@/Components/ui/Input';
import { Label } from '@/Components/ui/Label';

export default function Edit({ seller }) {
    const { data, setData, patch, processing, errors } = useForm({
        name: seller.name ?? '',
        trade_name: seller.trade_name ?? '',
        email: seller.email ?? '',
        address_street: seller.address_street ?? '',
        address_number: seller.address_number ?? '',
        address_complement: seller.address_complement ?? '',
        address_district: seller.address_district ?? '',
        address_city: seller.address_city ?? '',
        address_state: seller.address_state ?? '',
        address_zip: seller.address_zip ?? '',
    });

    function submit(event) {
        event.preventDefault();
        patch(route('sellers.update', seller.id));
    }

    const fields = [
        ['name', 'Name'],
        ['trade_name', 'Trade Name'],
        ['email', 'Email'],
        ['address_street', 'Street'],
        ['address_number', 'Number'],
        ['address_complement', 'Complement'],
        ['address_district', 'District'],
        ['address_city', 'City'],
        ['address_state', 'State (UF)'],
        ['address_zip', 'ZIP'],
    ];

    return (
        <AppLayout header={<h2 className="text-base font-semibold text-foreground">Edit Seller</h2>}>
            <Head title="Edit Seller" />

            <div className="space-y-4">
                <div className="mx-auto max-w-2xl sm:px-6 lg:px-8">
                    <form onSubmit={submit} className="space-y-4 rounded-md border bg-white p-6">
                        {fields.map(([field, label]) => (
                            <div key={field} className="space-y-2">
                                <Label htmlFor={field}>{label}</Label>
                                <Input
                                    id={field}
                                    value={data[field]}
                                    onChange={(event) => setData(field, event.target.value)}
                                />
                                {errors[field] && <p className="text-sm text-destructive">{errors[field]}</p>}
                            </div>
                        ))}

                        <Button type="submit" disabled={processing}>
                            Save
                        </Button>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
