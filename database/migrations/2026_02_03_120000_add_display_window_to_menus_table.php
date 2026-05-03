<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dateTime('tampil_mulai')->nullable()->after('status_aktif');
            $table->dateTime('tampil_sampai')->nullable()->after('tampil_mulai');
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropColumn(['tampil_mulai', 'tampil_sampai']);
        });
    }
};
