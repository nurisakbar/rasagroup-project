<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Fitur diskon distributor ke outlet dihapus: buang tabel outlet_category_discounts dan
 * kolom users.outlet_discount_max (aman dijalankan di DB yang tidak pernah memilikinya).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::dropIfExists('outlet_category_discounts');

        if (Schema::hasColumn('users', 'outlet_discount_max')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('outlet_discount_max');
            });
        }
    }

    public function down(): void
    {
        // Fitur sudah dihapus; tidak ada yang perlu dikembalikan.
    }
};
