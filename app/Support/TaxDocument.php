<?php

namespace App\Support;

/**
 * CPF/CNPJ validation with modulo-11 checksum (Receita Federal algorithm).
 */
class TaxDocument
{
    /** Keep only digits. */
    public static function sanitize(string $document): string
    {
        return preg_replace('/\D/', '', $document) ?? '';
    }

    /** Valid CPF (11 digits) or CNPJ (14 digits), by length. */
    public static function isValid(string $document): bool
    {
        $digits = self::sanitize($document);

        return match (strlen($digits)) {
            11 => self::isValidCpf($digits),
            14 => self::isValidCnpj($digits),
            default => false,
        };
    }

    public static function isValidCpf(string $document): bool
    {
        $cpf = self::sanitize($document);

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($t = 9; $t < 11; $t++) {
            $sum = 0;
            for ($i = 0; $i < $t; $i++) {
                $sum += (int) $cpf[$i] * (($t + 1) - $i);
            }
            $digit = ((10 * $sum) % 11) % 10;
            if ((int) $cpf[$t] !== $digit) {
                return false;
            }
        }

        return true;
    }

    public static function isValidCnpj(string $document): bool
    {
        $cnpj = self::sanitize($document);

        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj)) {
            return false;
        }

        $weightsFirst = [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];
        $weightsSecond = [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2];

        foreach ([12 => $weightsFirst, 13 => $weightsSecond] as $position => $weights) {
            $sum = 0;
            for ($i = 0; $i < $position; $i++) {
                $sum += (int) $cnpj[$i] * $weights[$i];
            }
            $remainder = $sum % 11;
            $digit = $remainder < 2 ? 0 : 11 - $remainder;
            if ((int) $cnpj[$position] !== $digit) {
                return false;
            }
        }

        return true;
    }
}
