<?php

namespace App\Http\Controllers;

use App\Actions\Issuer\StoreCertificateAction;
use App\Actions\Issuer\UpsertIssuerAction;
use App\Actions\Issuer\ValidateWithPortalAction;
use App\Enums\AmbienteEmissao;
use App\Enums\EmissionMode;
use App\Enums\RegimeTributario;
use App\Exceptions\CertificateException;
use App\Exceptions\PortalValidationException;
use App\Http\Requests\Issuer\UpdateIssuerRequest;
use App\Http\Requests\Issuer\UploadCertificateRequest;
use App\Models\Issuer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class IssuerController extends Controller
{
    public function edit(Request $request): Response
    {
        $issuer = $request->user()->issuer;

        return Inertia::render('Settings/Fiscal', [
            'issuer' => $issuer,
            'certificate' => $issuer && $issuer->hasCertificate() ? [
                'subject_cn' => $issuer->certificate_subject_cn,
                'document' => $issuer->certificate_document,
                'valid_until' => $issuer->certificate_valid_until,
                'expired' => $issuer->certificateExpired(),
            ] : null,
            'requirements' => $this->requirements($issuer),
            'options' => [
                'regime_tributario' => $this->enumOptions(RegimeTributario::cases()),
                'ambiente' => $this->enumOptions(AmbienteEmissao::cases()),
                'emission_mode' => $this->enumOptions(EmissionMode::cases()),
            ],
        ]);
    }

    public function update(UpdateIssuerRequest $request, UpsertIssuerAction $action): RedirectResponse
    {
        $action->handle($request->user(), $request->validated());

        return redirect()->route('issuer.edit')->with('status', 'Cadastro fiscal atualizado.');
    }

    public function uploadCertificate(UploadCertificateRequest $request, StoreCertificateAction $action): RedirectResponse
    {
        $issuer = $request->user()->issuer;

        if (! $issuer) {
            return back()->withErrors(['certificate' => 'Preencha o cadastro fiscal antes de enviar o certificado.']);
        }

        try {
            $action->handle($issuer, $request->file('certificate'), $request->string('certificate_password')->value());
        } catch (CertificateException $e) {
            return back()->withErrors(['certificate' => $e->getMessage()]);
        }

        return redirect()->route('issuer.edit')->with('status', 'Certificado digital armazenado com segurança.');
    }

    public function validatePortal(Request $request, ValidateWithPortalAction $action): RedirectResponse
    {
        $issuer = $request->user()->issuer;

        if (! $issuer) {
            return back()->withErrors(['portal' => 'Preencha o cadastro fiscal antes de validar com o portal.']);
        }

        try {
            $action->handle($issuer);
        } catch (PortalValidationException $e) {
            return back()->withErrors(['portal' => $e->getMessage()]);
        }

        return redirect()->route('issuer.edit')->with('status', 'Validado com o portal nacional com sucesso.');
    }

    /**
     * @return array<string, bool>
     */
    private function requirements(?Issuer $issuer): array
    {
        return [
            'fiscal_data' => $issuer !== null
                && $issuer->legal_name !== null
                && $issuer->address_ibge_code !== null
                && $issuer->service_code !== null,
            'certificate' => $issuer !== null && $issuer->hasValidCertificate(),
            'portal_validated' => $issuer !== null && $issuer->isPortalValidated(),
        ];
    }

    /**
     * @param  array<int, RegimeTributario|AmbienteEmissao|EmissionMode>  $cases
     * @return array<int, array{value: string, label: string}>
     */
    private function enumOptions(array $cases): array
    {
        return array_map(
            fn ($case) => ['value' => $case->value, 'label' => $case->label()],
            $cases,
        );
    }
}
