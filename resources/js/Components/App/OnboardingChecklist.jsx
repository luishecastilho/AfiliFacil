import { Link, usePage } from '@inertiajs/react';
import { Check, Lock } from 'lucide-react';
import { Button } from '@/Components/ui/Button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/Card';
import { ProgressBar } from '@/Components/ProgressBar';
import { cn } from '@/lib/cn';

export function OnboardingChecklist({ summary }) {
    const { fiscal } = usePage().props;
    const missing = fiscal?.missing ?? ['cadastro_fiscal'];
    const isManual = fiscal?.mode === 'manual';
    const has = (key) => !missing.includes(key);

    const dadosOk = has('cadastro_fiscal') && has('dados_fiscais') && has('endereco') && has('codigo_servico');
    const fiscalComplete = fiscal?.complete ?? false;
    const hasImports = (summary?.total_imports ?? 0) > 0;
    const hasInvoices = (summary?.total_invoices ?? 0) > 0;

    const steps = [
        {
            title: 'Complete seu cadastro fiscal',
            why: 'CNPJ, endereço e código de serviço são exigidos em toda nota fiscal.',
            done: dadosOk,
            locked: false,
            href: route('issuer.edit'),
            cta: 'Preencher cadastro',
        },
        isManual
            ? {
                  title: 'Conecte sua conta gov.br',
                  why: 'A identidade gov.br habilita a emissão manual assistida.',
                  done: has('govbr'),
                  locked: !dadosOk,
                  href: route('issuer.edit'),
                  cta: 'Conectar gov.br',
              }
            : {
                  title: 'Envie seu certificado digital A1',
                  why: 'O certificado autoriza a emissão automática em seu nome.',
                  done: has('certificado'),
                  locked: !dadosOk,
                  href: route('issuer.edit'),
                  cta: 'Enviar certificado',
              },
        ...(isManual
            ? []
            : [
                  {
                      title: 'Valide com o portal nacional',
                      why: 'Confirma que seu certificado e seu município funcionam no portal.',
                      done: has('validacao_portal'),
                      locked: !has('certificado'),
                      href: route('issuer.edit'),
                      cta: 'Validar agora',
                  },
              ]),
        {
            title: 'Importe seu relatório de comissões',
            why: 'Envie o relatório da Shopee para a plataforma preparar as notas.',
            done: hasImports,
            locked: !fiscalComplete,
            href: route('imports.create'),
            cta: 'Importar relatório',
        },
        {
            title: 'Gere e baixe suas notas',
            why: 'Depois de importar, gere as notas e baixe o PDF e o XML.',
            done: hasInvoices,
            locked: !hasImports,
            href: route('invoices.index'),
            cta: 'Ver notas',
        },
    ];

    const doneCount = steps.filter((s) => s.done).length;
    const currentIndex = steps.findIndex((s) => !s.done && !s.locked);

    return (
        <Card>
            <CardHeader>
                <CardTitle className="text-lg">Primeiros passos</CardTitle>
                <CardDescription>Siga os passos abaixo para emitir sua primeira nota fiscal.</CardDescription>
                <div className="flex items-center gap-3 pt-2">
                    <ProgressBar value={doneCount} max={steps.length} className="max-w-xs" />
                    <span className="text-sm text-muted-foreground">
                        {doneCount} de {steps.length}
                    </span>
                </div>
            </CardHeader>
            <CardContent className="space-y-3">
                {steps.map((step, index) => {
                    const isCurrent = index === currentIndex;
                    return (
                        <div
                            key={step.title}
                            className={cn(
                                'flex items-start gap-3 rounded-lg border p-3',
                                isCurrent && 'border-primary bg-primary/5',
                                step.done && 'opacity-70',
                            )}
                        >
                            <div
                                className={cn(
                                    'mt-0.5 flex size-6 shrink-0 items-center justify-center rounded-full text-xs font-semibold',
                                    step.done
                                        ? 'bg-emerald-600 text-white'
                                        : step.locked
                                          ? 'bg-muted text-muted-foreground'
                                          : 'bg-primary text-primary-foreground',
                                )}
                            >
                                {step.done ? <Check className="size-4" /> : step.locked ? <Lock className="size-3" /> : index + 1}
                            </div>
                            <div className="min-w-0 flex-1">
                                <p className="text-sm font-medium">{step.title}</p>
                                <p className="text-xs text-muted-foreground">{step.why}</p>
                            </div>
                            {!step.done && (
                                <Button asChild size="sm" variant={isCurrent ? 'default' : 'outline'} disabled={step.locked}>
                                    {step.locked ? (
                                        <span className="cursor-not-allowed opacity-50">{step.cta}</span>
                                    ) : (
                                        <Link href={step.href}>{step.cta}</Link>
                                    )}
                                </Button>
                            )}
                        </div>
                    );
                })}
            </CardContent>
        </Card>
    );
}
