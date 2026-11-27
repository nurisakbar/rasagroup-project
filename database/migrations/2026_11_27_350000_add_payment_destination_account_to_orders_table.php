<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rekening tujuan yang dipilih pembeli saat konfirmasi pembayaran ke distributor (salinan teks: bank, nomor, nama),
 * tetap utuh walau rekening distributor kemudian diubah/dihapus.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_destination_account')->nullable()->after('payment_due_at');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('payment_destination_account');
        });
    }
};
