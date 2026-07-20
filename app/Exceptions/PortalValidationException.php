<?php

namespace App\Exceptions;

use Exception;

class PortalValidationException extends Exception
{
    public static function certificateRequired(): self
    {
        return new self('É necessário um certificado A1 válido antes de validar com o portal nacional.');
    }

    public static function fromStatus(int $status): self
    {
        return new self(match (true) {
            in_array($status, [401, 403], true) => 'Certificado rejeitado pelo portal nacional. Verifique se o certificado é válido e corresponde ao emitente.',
            $status === 404 => 'Seu município ainda não está aderente ao Padrão Nacional NFS-e.',
            $status >= 500 => 'O portal nacional está indisponível no momento. Tente novamente mais tarde.',
            default => "Não foi possível validar com o portal nacional (HTTP {$status}).",
        });
    }
}
