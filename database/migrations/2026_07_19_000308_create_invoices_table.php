<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained()->restrictOnDelete();
            $table->foreignId('seller_id')->constrained()->restrictOnDelete();
            $table->enum('status', [
                'queued', 'processing', 'generated', 'failed', 'cancelled', 'retrying',
            ])->default('queued');
            $table->char('reference_month', 7);
            $table->decimal('amount', 15, 2);
            $table->string('invoice_number', 50)->nullable();
            $table->string('access_key', 50)->nullable();
            $table->timestamp('issued_at')->nullable();
            $table->string('provider', 100)->nullable();
            $table->string('provider_reference')->nullable();
            $table->json('provider_payload')->nullable();
            $table->unsignedTinyInteger('retry_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->unique(['import_id', 'seller_id', 'reference_month']);
            $table->index(['import_id', 'status']);
            $table->index('seller_id');
            $table->index('access_key');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
