<?php

namespace App\Exceptions;

use Exception;

class CertificateException extends Exception
{
    public static function invalidPassword(): self
    {
        return new self('Não foi possível ler o certificado: senha incorreta ou arquivo inválido.');
    }

    public static function expired(): self
    {
        return new self('O certificado digital está expirado.');
    }

    public static function documentMismatch(): self
    {
        return new self('O CNPJ/CPF do certificado não corresponde ao do emitente cadastrado.');
    }

    public static function notFound(): self
    {
        return new self('Nenhum certificado digital foi encontrado para este emitente.');
    }
}
