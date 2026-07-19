<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->restrictOnDelete();
            $table->enum('type', ['pdf', 'xml', 'zip']);
            $table->string('disk', 50)->default('s3');
            $table->string('storage_path', 500);
            $table->unsignedBigInteger('size')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_files');
    }
};
