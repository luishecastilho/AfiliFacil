<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->foreignId('issuer_id')->nullable()->after('seller_id')->constrained()->nullOnDelete();
            $table->string('dps_serie', 5)->nullable()->after('access_key');
            $table->unsignedBigInteger('dps_numero')->nullable()->after('dps_serie');
            $table->string('service_code', 20)->nullable()->after('dps_numero');
            $table->decimal('iss_rate', 7, 4)->nullable()->after('service_code');
            $table->decimal('iss_amount', 15, 2)->nullable()->after('iss_rate');
            $table->string('ambiente', 20)->nullable()->after('iss_amount');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropConstrainedForeignId('issuer_id');
            $table->dropColumn(['dps_serie', 'dps_numero', 'service_code', 'iss_rate', 'iss_amount', 'ambiente']);
        });
    }
};
