import AppLayout from '@/Layouts/AppLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useState } from 'react';
import { CheckCircle2, Circle, ShieldCheck } from 'lucide-react';

import { FieldHint, FieldLabel } from '@/Components/FieldHelp';
import { FileUploadZone } from '@/Components/FileUploadZone';
import { Alert } from '@/Components/ui/Alert';
import { Badge } from '@/Components/ui/Badge';
import { Button } from '@/Components/ui/Button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/Card';
import { Input } from '@/Components/ui/Input';
import { Label } from '@/Components/ui/Label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/Components/ui/Select';

function Field({ id, label, value, onChange, error, type = 'text', placeholder, help, hint, onBlur }) {
    return (
        <div className="space-y-1.5">
            <FieldLabel htmlFor={id} help={help}>
                {label}
            </FieldLabel>
            <Input
                id={id}
                type={type}
                value={value}
                placeholder={placeholder}
                onBlur={onBlur}
                onChange={(e) => onChange(e.target.value)}
            />
            {hint && <FieldHint>{hint}</FieldHint>}
            {error && <p className="text-sm text-destructive">{error}</p>}
        </div>
    );
}

function SelectField({ label, value, onChange, options, error, help }) {
    return (
        <div className="space-y-1.5">
            <FieldLabel help={help}>{label}</FieldLabel>
            <Select value={value} onValueChange={onChange}>
                <SelectTrigger>
                    <SelectValue placeholder="Selecione…" />
                </SelectTrigger>
                <SelectContent>
                    {options.map((opt) => (
                        <SelectItem key={opt.value} value={opt.value}>
                            {opt.label}
                        </SelectItem>
                    ))}
                </SelectContent>
            </Select>
            {error && <p className="text-sm text-destructive">{error}</p>}
        </div>
    );
}

function ChecklistItem({ done, children }) {
    return (
        <li className="flex items-center gap-2 text-sm">
            {done ? (
                <CheckCircle2 className="size-4 text-emerald-600" />
            ) : (
                <Circle className="size-4 text-muted-foreground" />
            )}
            <span className={done ? 'text-foreground' : 'text-muted-foreground'}>{children}</span>
        </li>
    );
}

export default function Fiscal({ issuer, certificate, requirements, options }) {
    const form = useForm({
        tax_document: issuer?.tax_document ?? '',
        legal_name: issuer?.legal_name ?? '',
        trade_name: issuer?.trade_name ?? '',
        inscricao_municipal: issuer?.inscricao_municipal ?? '',
        address_street: issuer?.address_street ?? '',
        address_number: issuer?.address_number ?? '',
        address_complement: issuer?.address_complement ?? '',
        address_district: issuer?.address_district ?? '',
        address_city: issuer?.address_city ?? '',
        address_state: issuer?.address_state ?? '',
        address_zip: issuer?.address_zip ?? '',
        address_ibge_code: issuer?.address_ibge_code ?? '',
        regime_tributario: issuer?.regime_tributario ?? 'simples_nacional',
        service_code: issuer?.service_code ?? '',
        municipal_service_code: issuer?.municipal_service_code ?? '',
        cnae: issuer?.cnae ?? '',
        iss_rate: issuer?.iss_rate ?? '',
        iss_withheld: issuer?.iss_withheld ?? false,
        ambiente: issuer?.ambiente ?? 'producao_restrita',
        emission_mode: issuer?.emission_mode ?? 'automated',
        dps_serie: issuer?.dps_serie ?? '00001',
    });

    const certForm = useForm({ certificate: null, certificate_password: '' });
    const portalForm = useForm({});
    const [cepStatus, setCepStatus] = useState('idle');

    const set = (field) => (value) => form.setData(field, value);

    async function lookupCep() {
        const digits = (form.data.address_zip || '').replace(/\D/g, '');
        if (digits.length !== 8) return;

        setCepStatus('loading');
        try {
            const res = await fetch(route('cep.lookup', digits), { headers: { Accept: 'application/json' } });
            if (!res.ok) {
                setCepStatus('error');
                return;
            }
            const d = await res.json();
            form.setData('address_street', d.address_street || form.data.address_street);
            form.setData('address_district', d.address_district || form.data.address_district);
            form.setData('address_city', d.address_city || form.data.address_city);
            form.setData('address_state', d.address_state || form.data.address_state);
            form.setData('address_ibge_code', d.address_ibge_code || form.data.address_ibge_code);
            setCepStatus('done');
        } catch {
            setCepStatus('error');
        }
    }

    function validatePortal() {
        portalForm.post(route('issuer.validate'), { preserveScroll: true });
    }

    function submit(event) {
        event.preventDefault();
        form.post(route('issuer.update'), { preserveScroll: true });
    }

    function submitCertificate(event) {
        event.preventDefault();
        certForm.post(route('issuer.certificate'), {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => certForm.reset('certificate', 'certificate_password'),
        });
    }

    const isAutomated = form.data.emission_mode === 'automated';
    const isReady =
        requirements.fiscal_data && (isAutomated ? requirements.certificate && requirements.portal_validated : true);

    const cepHint =
        cepStatus === 'loading'
            ? 'Buscando endereço…'
            : cepStatus === 'error'
              ? 'CEP não encontrado — preencha o endereço manualmente.'
              : cepStatus === 'done'
                ? 'Endereço preenchido automaticamente ✓'
                : 'Digite o CEP para preencher o endereço automaticamente. Ex.: 01001-000';

    return (
        <AppLayout header={<h2 className="text-base font-semibold text-foreground">Cadastro Fiscal</h2>}>
            <Head title="Cadastro Fiscal" />

            <div className="mx-auto max-w-3xl space-y-6 sm:px-6 lg:px-8">
                {isReady && (
                    <Alert
                        variant="success"
                        title="Tudo pronto para emitir!"
                        action={
                            <Button asChild size="sm">
                                <Link href={route('imports.create')}>Importar meu primeiro relatório</Link>
                            </Button>
                        }
                    >
                        Seu cadastro fiscal está completo e validado.
                    </Alert>
                )}

                {/* Prontidão */}
                <Card>
                    <CardHeader>
                        <CardTitle className="flex items-center gap-2 text-lg">
                            <ShieldCheck className="size-5" /> Prontidão para emissão
                        </CardTitle>
                        <CardDescription>
                            Complete os itens abaixo para liberar a emissão de NFS-e e a assinatura de um plano.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <ul className="space-y-2">
                            <ChecklistItem done={requirements.fiscal_data}>
                                Dados fiscais e endereço completos
                            </ChecklistItem>
                            {isAutomated ? (
                                <>
                                    <ChecklistItem done={requirements.certificate}>
                                        Certificado A1 válido enviado
                                    </ChecklistItem>
                                    <ChecklistItem done={requirements.portal_validated}>
                                        Validado com o portal nacional
                                    </ChecklistItem>
                                </>
                            ) : (
                                <ChecklistItem done={false}>Conta gov.br conectada (modo manual)</ChecklistItem>
                            )}
                        </ul>

                        {isAutomated && requirements.certificate && (
                            <div className="mt-4 space-y-2">
                                <p className="text-sm text-muted-foreground">
                                    A validação faz uma consulta real ao portal nacional com o seu certificado, confirmando
                                    que ele funciona e que seu município está aderente.
                                </p>
                                <div className="flex items-center gap-3">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        size="sm"
                                        onClick={validatePortal}
                                        disabled={portalForm.processing}
                                    >
                                        {requirements.portal_validated
                                            ? 'Revalidar com o portal'
                                            : 'Validar com o portal nacional'}
                                    </Button>
                                    {portalForm.errors.portal && (
                                        <span className="text-sm text-destructive">{portalForm.errors.portal}</span>
                                    )}
                                </div>
                            </div>
                        )}
                    </CardContent>
                </Card>

                <form onSubmit={submit} className="space-y-6">
                    {/* Dados fiscais */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg">Dados fiscais</CardTitle>
                        </CardHeader>
                        <CardContent className="grid gap-4 sm:grid-cols-2">
                            <Field
                                id="tax_document"
                                label="CNPJ / CPF"
                                value={form.data.tax_document}
                                onChange={set('tax_document')}
                                error={form.errors.tax_document}
                                placeholder="Somente números"
                                help="O CNPJ da sua empresa/MEI (ou CPF). Deve ser o mesmo do certificado digital."
                            />
                            <SelectField
                                label="Regime tributário"
                                value={form.data.regime_tributario}
                                onChange={set('regime_tributario')}
                                options={options.regime_tributario}
                                error={form.errors.regime_tributario}
                                help="Como sua empresa é tributada. A maioria dos afiliados é MEI ou Simples Nacional."
                            />
                            <Field
                                id="legal_name"
                                label="Razão social"
                                value={form.data.legal_name}
                                onChange={set('legal_name')}
                                error={form.errors.legal_name}
                                help="O nome oficial da empresa, como consta no cartão CNPJ."
                            />
                            <Field
                                id="trade_name"
                                label="Nome fantasia"
                                value={form.data.trade_name}
                                onChange={set('trade_name')}
                                error={form.errors.trade_name}
                                help="O nome comercial, se houver. Opcional."
                            />
                            <Field
                                id="inscricao_municipal"
                                label="Inscrição municipal"
                                value={form.data.inscricao_municipal}
                                onChange={set('inscricao_municipal')}
                                error={form.errors.inscricao_municipal}
                                help="Número de cadastro na prefeitura. Encontra-se no alvará ou no portal da prefeitura. Alguns municípios não exigem."
                            />
                        </CardContent>
                    </Card>

                    {/* Endereço */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg">Endereço</CardTitle>
                            <CardDescription>
                                Comece pelo CEP — preenchemos o resto (inclusive o código do município) para você.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-4 sm:grid-cols-2">
                            <Field
                                id="address_zip"
                                label="CEP"
                                value={form.data.address_zip}
                                onChange={set('address_zip')}
                                onBlur={lookupCep}
                                error={form.errors.address_zip}
                                hint={cepHint}
                            />
                            <Field
                                id="address_street"
                                label="Logradouro"
                                value={form.data.address_street}
                                onChange={set('address_street')}
                                error={form.errors.address_street}
                            />
                            <Field
                                id="address_number"
                                label="Número"
                                value={form.data.address_number}
                                onChange={set('address_number')}
                                error={form.errors.address_number}
                            />
                            <Field
                                id="address_complement"
                                label="Complemento"
                                value={form.data.address_complement}
                                onChange={set('address_complement')}
                                error={form.errors.address_complement}
                            />
                            <Field
                                id="address_district"
                                label="Bairro"
                                value={form.data.address_district}
                                onChange={set('address_district')}
                                error={form.errors.address_district}
                            />
                            <Field
                                id="address_city"
                                label="Cidade"
                                value={form.data.address_city}
                                onChange={set('address_city')}
                                error={form.errors.address_city}
                            />
                            <Field
                                id="address_state"
                                label="UF"
                                value={form.data.address_state}
                                onChange={set('address_state')}
                                error={form.errors.address_state}
                            />
                            <Field
                                id="address_ibge_code"
                                label="Código do município (IBGE)"
                                value={form.data.address_ibge_code}
                                onChange={set('address_ibge_code')}
                                error={form.errors.address_ibge_code}
                                help="Código de 7 dígitos que identifica seu município. Preenchido automaticamente pelo CEP."
                                hint="Preenchido automaticamente ao informar o CEP."
                            />
                        </CardContent>
                    </Card>

                    {/* Serviço & tributação */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg">Serviço e tributação</CardTitle>
                            <CardDescription>
                                Em dúvida sobre estes campos? Seu contador informa os valores corretos em segundos.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-4 sm:grid-cols-2">
                            <Field
                                id="service_code"
                                label="Código do serviço (LC 116)"
                                value={form.data.service_code}
                                onChange={set('service_code')}
                                error={form.errors.service_code}
                                placeholder="Ex.: 10.05"
                                help="Código do tipo de serviço na Lei Complementar 116. Para intermediação/agenciamento (comissão de afiliado) costuma ser 10.05."
                            />
                            <Field
                                id="municipal_service_code"
                                label="Código de serviço municipal"
                                value={form.data.municipal_service_code}
                                onChange={set('municipal_service_code')}
                                error={form.errors.municipal_service_code}
                                help="Código do serviço usado pela sua prefeitura, quando exigido. Opcional."
                            />
                            <Field
                                id="cnae"
                                label="CNAE"
                                value={form.data.cnae}
                                onChange={set('cnae')}
                                error={form.errors.cnae}
                                help="Código da atividade econômica da empresa, no cartão CNPJ. Opcional."
                            />
                            <Field
                                id="iss_rate"
                                label="Alíquota ISS (%)"
                                type="number"
                                value={form.data.iss_rate}
                                onChange={set('iss_rate')}
                                error={form.errors.iss_rate}
                                help="Percentual do imposto municipal (ISS) sobre o serviço. Para MEI costuma ser fixo; seu contador confirma."
                            />
                            <Field
                                id="dps_serie"
                                label="Série da nota (DPS)"
                                value={form.data.dps_serie}
                                onChange={set('dps_serie')}
                                error={form.errors.dps_serie}
                                help="Série da numeração das suas notas. Pode deixar o valor padrão."
                            />
                            <div className="flex items-center gap-2 pt-8">
                                <input
                                    id="iss_withheld"
                                    type="checkbox"
                                    className="size-4 rounded border-input"
                                    checked={form.data.iss_withheld}
                                    onChange={(e) => form.setData('iss_withheld', e.target.checked)}
                                />
                                <FieldLabel htmlFor="iss_withheld" help="Marque apenas se quem contrata o serviço recolhe o ISS no seu lugar. Na dúvida, deixe desmarcado.">
                                    ISS retido pelo tomador
                                </FieldLabel>
                            </div>
                        </CardContent>
                    </Card>

                    {/* Modo de emissão */}
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg">Modo de emissão</CardTitle>
                            <CardDescription>
                                <strong>Automatizado</strong> emite as notas sozinho pela plataforma (precisa de certificado
                                A1). <strong>Manual</strong> prepara as notas para você emitir no Emissor Nacional.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="grid gap-4 sm:grid-cols-2">
                            <SelectField
                                label="Modo"
                                value={form.data.emission_mode}
                                onChange={set('emission_mode')}
                                options={options.emission_mode}
                                error={form.errors.emission_mode}
                            />
                            <SelectField
                                label="Ambiente"
                                value={form.data.ambiente}
                                onChange={set('ambiente')}
                                options={options.ambiente}
                                error={form.errors.ambiente}
                                help="Use “Produção restrita” para testes e “Produção” para emitir notas válidas de verdade."
                            />
                        </CardContent>
                    </Card>

                    <div className="flex items-center gap-3">
                        <Button type="submit" disabled={form.processing}>
                            Salvar cadastro fiscal
                        </Button>
                        {form.recentlySuccessful && <span className="text-sm text-emerald-600">Salvo.</span>}
                    </div>
                </form>

                {/* Certificado A1 */}
                {isAutomated && (
                    <Card>
                        <CardHeader>
                            <CardTitle className="text-lg">Certificado digital A1</CardTitle>
                            <CardDescription>
                                Necessário para a plataforma emitir notas automaticamente em seu nome.
                            </CardDescription>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <Alert variant="info" title="O que é o certificado A1?">
                                É um arquivo digital (.pfx ou .p12) — o e-CNPJ A1 — emitido por uma autoridade certificadora
                                ICP-Brasil (ex.: Serasa, Certisign, Valid). Custa cerca de R$ 130–250 por ano. A senha é a que
                                você definiu ao comprá-lo. Guardamos o arquivo de forma criptografada e nunca o exibimos de volta.
                            </Alert>

                            {certificate && (
                                <div className="flex flex-wrap items-center gap-3 rounded-md border bg-muted/40 p-3 text-sm">
                                    <Badge variant={certificate.expired ? 'destructive' : 'secondary'}>
                                        {certificate.expired ? 'Expirado' : 'Ativo'}
                                    </Badge>
                                    <span className="text-muted-foreground">{certificate.subject_cn}</span>
                                    {certificate.valid_until && (
                                        <span className="text-muted-foreground">
                                            Válido até {new Date(certificate.valid_until).toLocaleDateString('pt-BR')}
                                        </span>
                                    )}
                                </div>
                            )}

                            <form onSubmit={submitCertificate} className="space-y-4">
                                <FileUploadZone
                                    accept=".pfx,.p12"
                                    helperText="Certificado A1 (.pfx ou .p12)"
                                    file={certForm.data.certificate}
                                    onFileSelect={(file) => certForm.setData('certificate', file)}
                                />
                                {certForm.errors.certificate && (
                                    <p className="text-sm text-destructive">{certForm.errors.certificate}</p>
                                )}

                                <div className="space-y-2">
                                    <Label htmlFor="certificate_password">Senha do certificado</Label>
                                    <Input
                                        id="certificate_password"
                                        type="password"
                                        value={certForm.data.certificate_password}
                                        onChange={(e) => certForm.setData('certificate_password', e.target.value)}
                                    />
                                    {certForm.errors.certificate_password && (
                                        <p className="text-sm text-destructive">{certForm.errors.certificate_password}</p>
                                    )}
                                </div>

                                <Button type="submit" disabled={certForm.processing || !certForm.data.certificate}>
                                    Enviar certificado
                                </Button>
                            </form>
                        </CardContent>
                    </Card>
                )}
            </div>
        </AppLayout>
    );
}
