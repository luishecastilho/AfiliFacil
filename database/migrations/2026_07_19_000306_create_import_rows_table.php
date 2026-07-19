<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('import_id')->constrained()->restrictOnDelete();
            $table->foreignId('seller_id')->nullable()->constrained()->restrictOnDelete();
            $table->unsignedInteger('row_number');
            $table->string('seller_name');
            $table->string('seller_document', 20);
            $table->string('seller_email')->nullable();
            $table->decimal('invoice_amount', 15, 2);
            $table->char('reference_month', 7);
            $table->enum('status', [
                'pending', 'valid', 'invalid', 'duplicate', 'queued', 'invoiced', 'failed',
            ])->default('pending');
            $table->json('validation_errors')->nullable();
            $table->json('payload');
            $table->timestamps();

            $table->index(['import_id', 'status']);
            $table->index(['import_id', 'seller_document']);
            $table->index('seller_id');
            $table->unique(['import_id', 'row_number']);
            $table->index(['seller_document', 'reference_month']);
        });

        // FULLTEXT indexes are MySQL-only; SQLite (used in local/testing) has no equivalent.
        if (Schema::getConnection()->getDriverName() === 'mysql') {
            Schema::table('import_rows', function (Blueprint $table) {
                $table->fullText('seller_name');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('import_rows');
    }
};
