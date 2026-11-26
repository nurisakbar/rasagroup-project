<?php

use App\Support\AdminAccess\DefaultModules;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Modul panel admin (menu dan cakupan rute), dikelola lewat menu Hak Akses.
        Schema::create('admin_modules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('key', 60)->unique();
            $table->string('name', 120);
            $table->string('group', 60)->nullable();          // mis. MASTER DATA: menu dikelompokkan dalam satu submenu
            $table->string('icon', 60)->default('fa-circle-o');
            $table->string('menu_route', 120)->nullable();    // nama rute tujuan menu
            $table->json('route_patterns');                   // pola nama rute, mis. ["admin.orders.*"]
            $table->unsignedInteger('sort_order')->default(100);
            $table->boolean('show_in_menu')->default(true);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false);     // modul bawaan: tidak bisa dihapus
            $table->timestamps();
        });

        // Izin per role per modul: lihat, tambah, ubah, hapus.
        Schema::create('admin_role_permissions', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('role', 50);
            $table->uuid('module_id');
            $table->boolean('can_view')->default(false);
            $table->boolean('can_create')->default(false);
            $table->boolean('can_edit')->default(false);
            $table->boolean('can_delete')->default(false);
            $table->timestamps();

            $table->unique(['role', 'module_id']);
            $table->foreign('module_id')->references('id')->on('admin_modules')->cascadeOnDelete();
        });

        // Data awal meniru akses menu yang sebelumnya di-hardcode, sehingga tidak ada perubahan perilaku.
        DefaultModules::seed();
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_role_permissions');
        Schema::dropIfExists('admin_modules');
    }
};
