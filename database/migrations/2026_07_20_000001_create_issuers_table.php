<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issuers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Identidade fiscal do emitente (prestador)
            $table->string('tax_document', 20);
            $table->enum('document_type', ['cpf', 'cnpj'])->default('cnpj');
            $table->string('legal_name');
            $table->string('trade_name')->nullable();
            $table->string('inscricao_municipal', 30)->nullable();

            // Endereço (espelha sellers)
            $table->string('address_street')->nullable();
            $table->string('address_number', 20)->nullable();
            $table->string('address_complement', 100)->nullable();
            $table->string('address_district', 100)->nullable();
            $table->string('address_city', 100)->nullable();
            $table->char('address_state', 2)->nullable();
            $table->string('address_zip', 10)->nullable();
            $table->string('address_ibge_code', 10)->nullable();

            // Parâmetros fiscais
            $table->enum('regime_tributario', ['mei', 'simples_nacional', 'normal'])->default('simples_nacional');
            $table->string('service_code', 20)->nullable();          // item LC 116 (ex.: 10.05)
            $table->string('municipal_service_code', 20)->nullable();
            $table->string('cnae', 10)->nullable();
            $table->decimal('iss_rate', 7, 4)->nullable();
            $table->boolean('iss_withheld')->default(false);
            $table->enum('ambiente', ['producao', 'producao_restrita'])->default('producao_restrita');

            // Modo de emissão
            $table->enum('emission_mode', ['automated', 'manual'])->default('automated');

            // Numeração DPS (Tier A) — alocada com lock na própria linha
            $table->string('dps_serie', 5)->default('00001');
            $table->unsignedBigInteger('dps_proximo_numero')->default(1);

            // Certificado A1 embutido (Tier A) — bytes cifrados no S3, senha cifrada no cast
            $table->string('certificate_path', 500)->nullable();
            $table->text('certificate_password')->nullable();
            $table->string('certificate_subject_cn')->nullable();
            $table->string('certificate_document', 20)->nullable();
            $table->timestamp('certificate_valid_from')->nullable();
            $table->timestamp('certificate_valid_until')->nullable();
            $table->timestamp('portal_validated_at')->nullable();

            // Identidade gov.br (Tier B, opcional)
            $table->string('govbr_sub')->nullable();
            $table->string('govbr_cpf', 14)->nullable();
            $table->timestamp('govbr_linked_at')->nullable();

            $table->timestamps();
            $table->softDeletes();

            $table->unique('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issuers');
    }
};
