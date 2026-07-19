<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('imports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->foreignId('marketplace_id')->constrained()->restrictOnDelete();
            $table->string('original_filename');
            $table->string('storage_path', 500);
            $table->string('disk', 50)->default('s3');
            $table->char('file_hash', 64);
            $table->unsignedBigInteger('file_size');
            $table->enum('status', [
                'pending', 'uploading', 'parsing', 'parsed', 'validating', 'validated', 'done', 'failed', 'cancelled',
            ])->default('pending');
            $table->unsignedInteger('total_rows')->nullable();
            $table->unsignedInteger('valid_rows')->nullable();
            $table->unsignedInteger('invalid_rows')->nullable();
            $table->unsignedInteger('duplicate_rows')->nullable();
            $table->decimal('total_amount', 15, 2)->nullable();
            $table->unsignedInteger('total_unique_tax_ids')->nullable();
            $table->char('reference_month', 7)->nullable();
            $table->timestamp('parsed_at')->nullable();
            $table->timestamp('imported_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'status']);
            $table->index('file_hash');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('imports');
    }
};
