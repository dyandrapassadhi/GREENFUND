<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('donasi', function (Blueprint $table) {
            $table->string('gateway', 20)->nullable()->after('status');      // 'doku'
            $table->string('gateway_ref', 64)->nullable()->after('gateway'); // invoice_number DOKU
            $table->text('payment_url')->nullable()->after('gateway_ref');
            $table->string('paid_at')->nullable()->after('payment_url');
        });
    }

    public function down(): void
    {
        Schema::table('donasi', function (Blueprint $table) {
            $table->dropColumn(['gateway', 'gateway_ref', 'payment_url', 'paid_at']);
        });
    }
};
