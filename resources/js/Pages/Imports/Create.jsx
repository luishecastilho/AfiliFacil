import AppLayout from '@/Layouts/AppLayout';
import { Head, useForm } from '@inertiajs/react';
import { FileUploadZone } from '@/Components/FileUploadZone';
import { Alert } from '@/Components/ui/Alert';
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
        <AppLayout header={<h2 className="text-base font-semibold text-foreground">Nova importação</h2>}>
            <Head title="Nova importação" />

            <div className="space-y-4">
                <div className="mx-auto max-w-2xl space-y-4 sm:px-6 lg:px-8">
                    <Alert variant="info" title="Qual arquivo enviar?">
                        Envie o <strong>relatório de comissões</strong> exportado do painel da Shopee (menu Afiliados →
                        Comissões → Exportar). Aceitamos arquivos <strong>CSV, XLSX ou XLS</strong>. A plataforma lê o
                        relatório, agrupa por afiliado e prepara as notas para você.
                    </Alert>

                    <form onSubmit={submit} className="space-y-6 rounded-md border bg-white p-6">
                        <div className="space-y-2">
                            <Label htmlFor="marketplace_id">Marketplace</Label>
                            <Select value={String(data.marketplace_id)} onValueChange={(value) => setData('marketplace_id', value)}>
                                <SelectTrigger id="marketplace_id">
                                    <SelectValue placeholder="Selecione o marketplace" />
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
                            <Label>Relatório de comissões</Label>
                            <FileUploadZone
                                file={data.file}
                                onFileSelect={(file) => setData('file', file)}
                                helperText="CSV, XLSX ou XLS"
                            />
                            {errors.file && <p className="text-sm text-destructive">{errors.file}</p>}
                        </div>

                        <Button type="submit" disabled={processing || !data.file}>
                            Enviar e processar
                        </Button>
                    </form>
                </div>
            </div>
        </AppLayout>
    );
}
