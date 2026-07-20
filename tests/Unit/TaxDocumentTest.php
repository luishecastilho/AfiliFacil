<?php

namespace Tests\Unit;

use App\Support\TaxDocument;
use PHPUnit\Framework\TestCase;

class TaxDocumentTest extends TestCase
{
    public function test_valid_cnpj_passes(): void
    {
        $this->assertTrue(TaxDocument::isValidCnpj('11222333000181'));
        $this->assertTrue(TaxDocument::isValid('11.222.333/0001-81'));
    }

    public function test_invalid_cnpj_fails(): void
    {
        $this->assertFalse(TaxDocument::isValidCnpj('11222333000180')); // wrong check digit
        $this->assertFalse(TaxDocument::isValidCnpj('11111111111111')); // repeated digits
        $this->assertFalse(TaxDocument::isValidCnpj('123'));            // wrong length
    }

    public function test_valid_cpf_passes(): void
    {
        $this->assertTrue(TaxDocument::isValidCpf('529.982.247-25'));
        $this->assertTrue(TaxDocument::isValid('52998224725'));
    }

    public function test_invalid_cpf_fails(): void
    {
        $this->assertFalse(TaxDocument::isValidCpf('52998224724')); // wrong check digit
        $this->assertFalse(TaxDocument::isValidCpf('00000000000')); // repeated digits
    }

    public function test_sanitize_strips_non_digits(): void
    {
        $this->assertSame('11222333000181', TaxDocument::sanitize('11.222.333/0001-81'));
    }
}
