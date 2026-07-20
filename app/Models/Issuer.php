<?php

namespace App\Models;

use App\Enums\AmbienteEmissao;
use App\Enums\EmissionMode;
use App\Enums\RegimeTributario;
use App\Models\Scopes\BelongsToUserScope;
use App\Observers\AuditObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy([BelongsToUserScope::class])]
#[ObservedBy([AuditObserver::class])]
class Issuer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'tax_document',
        'document_type',
        'legal_name',
        'trade_name',
        'inscricao_municipal',
        'address_street',
        'address_number',
        'address_complement',
        'address_district',
        'address_city',
        'address_state',
        'address_zip',
        'address_ibge_code',
        'regime_tributario',
        'service_code',
        'municipal_service_code',
        'cnae',
        'iss_rate',
        'iss_withheld',
        'ambiente',
        'emission_mode',
        'dps_serie',
    ];

    /**
     * Sensitive columns never exposed through serialization/Inertia props.
     *
     * @var list<string>
     */
    protected $hidden = [
        'certificate_path',
        'certificate_password',
    ];

    protected function casts(): array
    {
        return [
            'regime_tributario' => RegimeTributario::class,
            'ambiente' => AmbienteEmissao::class,
            'emission_mode' => EmissionMode::class,
            'iss_rate' => 'decimal:4',
            'iss_withheld' => 'boolean',
            'certificate_password' => 'encrypted',
            'certificate_valid_from' => 'datetime',
            'certificate_valid_until' => 'datetime',
            'portal_validated_at' => 'datetime',
            'govbr_linked_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function hasCertificate(): bool
    {
        return $this->certificate_path !== null;
    }

    public function certificateExpired(): bool
    {
        return $this->certificate_valid_until === null
            || $this->certificate_valid_until->isPast();
    }

    public function hasValidCertificate(): bool
    {
        return $this->hasCertificate() && ! $this->certificateExpired();
    }

    public function isPortalValidated(): bool
    {
        return $this->portal_validated_at !== null;
    }

    public function isGovbrLinked(): bool
    {
        return $this->govbr_linked_at !== null;
    }
}
