<?php

namespace App\Enums;

enum EmissionMode: string
{
    /** Emissão automática em lote via API nacional (exige certificado A1). */
    case Automated = 'automated';

    /** Emissão manual assistida no Emissor Nacional (identidade via gov.br). */
    case Manual = 'manual';

    public function label(): string
    {
        return match ($this) {
            self::Automated => 'Automatizado (certificado A1)',
            self::Manual => 'Manual assistido (gov.br)',
        };
    }

    public function requiresCertificate(): bool
    {
        return $this === self::Automated;
    }
}
