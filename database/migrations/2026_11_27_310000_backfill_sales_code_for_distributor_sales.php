<?php

use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Sales milik distributor yang belum punya kode diberi kode sales otomatis (MCR + nomor urut).
 */
return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function () {
            DB::table('users')
                ->where('role', 'sales')
                ->whereNotNull('distributor_id')
                ->where(fn ($q) => $q->whereNull('sales_code')->orWhere('sales_code', ''))
                ->orderBy('created_at')
                ->pluck('id')
                ->each(fn ($id) => DB::table('users')->where('id', $id)->update(['sales_code' => User::generateSalesCode()]));
        });
    }

    public function down(): void
    {
        // Kode yang sudah dibagikan tidak dicabut.
    }
};
