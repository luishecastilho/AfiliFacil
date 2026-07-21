import AppLayout from '@/Layouts/AppLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { Alert } from '@/Components/ui/Alert';
import { UpgradePrompt } from '@/Components/App/UpgradePrompt';
import { Button } from '@/Components/ui/Button';
import { SummaryCard } from '@/Components/SummaryCard';
import { useFreeTierStatus } from '@/hooks/useFreeTierStatus';
import { StatusBadge } from '@/Components/StatusBadge';
import { Pagination } from '@/Components/Pagination';
import { ProgressBar } from '@/Components/ProgressBar';
import { IMPORT_ROW_STATUS_LABELS, IMPORT_STATUS_LABELS } from '@/constants/statuses';
import { formatCurrency, formatReferenceMonth } from '@/lib/formatters';
import { useImportPoller } from '@/hooks/useImportPoller';
import { useInvoicePoller } from '@/hooks/useInvoicePoller';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/Table';

const TERMINAL_IMPORT_STATUSES = ['validated', 'done', 'failed', 'cancelled'];

export default function Show({ import: importRecord, rows }) {
    const [showInvalidWarning] = useState(importRecord.invalid_rows > 0);

    useImportPoller(importRecord.id, importRecord.status);

    const progress = useInvoicePoller(
        route('invoices.progress', importRecord.id),
        TERMINAL_IMPORT_STATUSES.includes(importRecord.status),
    );

    const { post, processing } = useForm();

    function generateInvoices() {
        post(route('invoices.generate', importRecord.id));
    }

    const freeTier = useFreeTierStatus();
    const uniqueAffiliates = importRecord.total_unique_tax_ids ?? 0;
    const exceedsQuota =
        freeTier.isFree && importRecord.status === 'validated' && uniqueAffiliates > freeTier.remaining;

    const totalInvoices = Object.values(progress).reduce((sum, count) => sum + count, 0);
    const generatedInvoices = progress.generated ?? 0;

    return (
        <AppLayout
            header={
                <h2 className="text-base font-semibold text-foreground">
                    Importação: {importRecord.original_filename}
                </h2>
            }
        >
            <Head title={`Importação #${importRecord.id}`} />

            <div className="space-y-6">
                <div className="space-y-6">
                    <div className="flex items-center justify-between rounded-md border bg-white p-4">
                        <StatusBadge status={importRecord.status} label={IMPORT_STATUS_LABELS[importRecord.status]} />

                        {importRecord.status === 'validated' && (
                            <Button onClick={generateInvoices} disabled={processing}>
                                Gerar todas as notas
                            </Button>
                        )}

                        {totalInvoices > 0 && (
                            <a href={route('imports.download.zip', importRecord.id)} className="text-sm underline">
                                Baixar tudo em ZIP
                            </a>
                        )}
                    </div>

                    {exceedsQuota && (
                        <UpgradePrompt
                            variant="warning"
                            title="Esta importação ultrapassa o limite do plano Gratuito"
                        >
                            Ela gera {uniqueAffiliates} notas, mas seu plano permite apenas mais {freeTier.remaining}{' '}
                            este mês. As notas excedentes falharão — faça upgrade para emitir todas de uma vez.
                        </UpgradePrompt>
                    )}

                    {totalInvoices > 0 && (
                        <div className="space-y-2 rounded-md border bg-white p-4">
                            <p className="text-sm text-muted-foreground">
                                {generatedInvoices} de {totalInvoices} notas geradas
                            </p>
                            <ProgressBar value={generatedInvoices} max={totalInvoices} />
                        </div>
                    )}

                    {showInvalidWarning && (
                        <Alert variant="warning" title={`${importRecord.invalid_rows} linha(s) com problema`}>
                            Essas linhas serão ignoradas na geração das notas. Para corrigir: baixe o relatório, ajuste os
                            dados (ex.: CNPJ/CPF inválido) e importe o arquivo novamente.
                        </Alert>
                    )}

                    <div className="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-6">
                        <SummaryCard title="Total de linhas" value={importRecord.total_rows ?? '—'} />
                        <SummaryCard title="Válidas" value={importRecord.valid_rows ?? '—'} />
                        <SummaryCard title="Inválidas" value={importRecord.invalid_rows ?? '—'} />
                        <SummaryCard title="Duplicadas" value={importRecord.duplicate_rows ?? '—'} />
                        <SummaryCard title="Comissão total" value={formatCurrency(importRecord.total_amount)} />
                        <SummaryCard title="Afiliados únicos" value={importRecord.total_unique_tax_ids ?? '—'} />
                    </div>

                    {rows && (
                        <div className="rounded-md border bg-white">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Afiliado</TableHead>
                                        <TableHead>Documento</TableHead>
                                        <TableHead>Valor</TableHead>
                                        <TableHead>Competência</TableHead>
                                        <TableHead>Status</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {rows.data.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>{row.seller_name}</TableCell>
                                            <TableCell>{row.seller_document}</TableCell>
                                            <TableCell>{formatCurrency(row.invoice_amount)}</TableCell>
                                            <TableCell>{formatReferenceMonth(row.reference_month)}</TableCell>
                                            <TableCell>
                                                <StatusBadge status={row.status} label={IMPORT_ROW_STATUS_LABELS[row.status]} />
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                            <Pagination links={rows.links} />
                        </div>
                    )}
                </div>
            </div>
        </AppLayout>
    );
}
