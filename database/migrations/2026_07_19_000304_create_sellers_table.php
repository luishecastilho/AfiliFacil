<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sellers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('tax_document', 20);
            $table->enum('document_type', ['cpf', 'cnpj'])->default('cnpj');
            $table->string('name');
            $table->string('trade_name')->nullable();
            $table->string('email')->nullable();
            $table->string('address_street')->nullable();
            $table->string('address_number', 20)->nullable();
            $table->string('address_complement', 100)->nullable();
            $table->string('address_district', 100)->nullable();
            $table->string('address_city', 100)->nullable();
            $table->char('address_state', 2)->nullable();
            $table->string('address_zip', 10)->nullable();
            $table->string('address_ibge_code', 10)->nullable();
            $table->timestamp('enriched_at')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'tax_document']);
            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sellers');
    }
};
