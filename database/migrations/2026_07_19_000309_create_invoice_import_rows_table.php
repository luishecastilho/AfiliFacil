<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_import_rows', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->restrictOnDelete();
            $table->foreignId('import_row_id')->constrained()->restrictOnDelete();
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['invoice_id', 'import_row_id']);
            $table->index('import_row_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_import_rows');
    }
};
