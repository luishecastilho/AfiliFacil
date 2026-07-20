<?php

namespace Tests\Feature;

use App\Models\Issuer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class IssuerTest extends TestCase
{
    use RefreshDatabase;

    private const CNPJ = '11222333000181';

    private function fiscalPayload(array $overrides = []): array
    {
        return array_merge([
            'tax_document' => '11.222.333/0001-81',
            'legal_name' => 'Empresa Teste LTDA',
            'trade_name' => 'Teste',
            'inscricao_municipal' => '123456',
            'address_street' => 'Rua A',
            'address_number' => '100',
            'address_district' => 'Centro',
            'address_city' => 'São Paulo',
            'address_state' => 'SP',
            'address_zip' => '01000000',
            'address_ibge_code' => '3550308',
            'regime_tributario' => 'simples_nacional',
            'service_code' => '10.05',
            'iss_rate' => '2.5',
            'iss_withheld' => false,
            'ambiente' => 'producao_restrita',
            'emission_mode' => 'automated',
            'dps_serie' => '00001',
        ], $overrides);
    }

    public function test_fiscal_page_renders(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('issuer.edit'))->assertOk();
    }

    public function test_issuer_is_created_with_sanitized_document(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('issuer.update'), $this->fiscalPayload())
            ->assertRedirect(route('issuer.edit'));

        $issuer = $user->issuer()->first();
        $this->assertNotNull($issuer);
        $this->assertSame(self::CNPJ, $issuer->tax_document);
        $this->assertSame('cnpj', $issuer->document_type);
    }

    public function test_invalid_document_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('issuer.update'), $this->fiscalPayload(['tax_document' => '11222333000180']))
            ->assertSessionHasErrors('tax_document');

        $this->assertNull($user->issuer()->first());
    }

    public function test_certificate_is_stored_encrypted(): void
    {
        Storage::fake('s3');
        $user = User::factory()->create();
        $issuer = $this->makeIssuer($user);

        $password = 'secret-pass';
        $pfx = $this->makePfx('EMPRESA TESTE:'.self::CNPJ, $password);
        $file = UploadedFile::fake()->createWithContent('cert.pfx', $pfx);

        $this->actingAs($user)
            ->post(route('issuer.certificate'), [
                'certificate' => $file,
                'certificate_password' => $password,
            ])
            ->assertRedirect(route('issuer.edit'));

        $issuer->refresh();
        $this->assertNotNull($issuer->certificate_path);
        $this->assertSame(self::CNPJ, $issuer->certificate_document);
        $this->assertNotNull($issuer->certificate_valid_until);

        Storage::disk('s3')->assertExists($issuer->certificate_path);
        $stored = Storage::disk('s3')->get($issuer->certificate_path);
        $this->assertNotSame($pfx, $stored, 'PFX must be encrypted at rest');
        $this->assertSame($pfx, Crypt::decryptString($stored));
        $this->assertSame($password, $issuer->certificate_password);
    }

    public function test_certificate_wrong_password_is_rejected(): void
    {
        Storage::fake('s3');
        $user = User::factory()->create();
        $issuer = $this->makeIssuer($user);

        $pfx = $this->makePfx('EMPRESA TESTE:'.self::CNPJ, 'right-pass');
        $file = UploadedFile::fake()->createWithContent('cert.pfx', $pfx);

        $this->actingAs($user)
            ->post(route('issuer.certificate'), [
                'certificate' => $file,
                'certificate_password' => 'wrong-pass',
            ])
            ->assertSessionHasErrors('certificate');

        $this->assertNull($issuer->refresh()->certificate_path);
    }

    public function test_certificate_document_mismatch_is_rejected(): void
    {
        Storage::fake('s3');
        $user = User::factory()->create();
        $issuer = $this->makeIssuer($user);

        $password = 'secret-pass';
        $pfx = $this->makePfx('OUTRA EMPRESA:99999999000191', $password); // different CNPJ
        $file = UploadedFile::fake()->createWithContent('cert.pfx', $pfx);

        $this->actingAs($user)
            ->post(route('issuer.certificate'), [
                'certificate' => $file,
                'certificate_password' => $password,
            ])
            ->assertSessionHasErrors('certificate');

        $this->assertNull($issuer->refresh()->certificate_path);
    }

    private function makeIssuer(User $user): Issuer
    {
        return $user->issuer()->create([
            'tax_document' => self::CNPJ,
            'document_type' => 'cnpj',
            'legal_name' => 'Empresa Teste LTDA',
            'regime_tributario' => 'simples_nacional',
            'ambiente' => 'producao_restrita',
            'emission_mode' => 'automated',
            'dps_serie' => '00001',
        ]);
    }

    private function makePfx(string $cn, string $password, int $daysValid = 365): string
    {
        $config = ['private_key_bits' => 2048, 'private_key_type' => OPENSSL_KEYTYPE_RSA];

        $pkey = @openssl_pkey_new($config);
        if ($pkey === false) {
            $this->markTestSkipped('OpenSSL key generation unavailable in this environment.');
        }

        $csr = @openssl_csr_new(['commonName' => $cn], $pkey, $config);
        if ($csr === false) {
            $this->markTestSkipped('OpenSSL CSR generation unavailable in this environment.');
        }

        $x509 = @openssl_csr_sign($csr, null, $pkey, $daysValid, $config);
        $pfx = '';
        @openssl_pkcs12_export($x509, $pfx, $pkey, $password);

        if ($pfx === '') {
            $this->markTestSkipped('OpenSSL PKCS#12 export unavailable in this environment.');
        }

        return $pfx;
    }
}
