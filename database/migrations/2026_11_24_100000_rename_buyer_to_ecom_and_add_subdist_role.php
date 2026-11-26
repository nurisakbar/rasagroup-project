<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Samakan nama jenis customer dengan nilai role:
 * - ecom      : daftar tanpa kode sales / kode distributor (sebelumnya "buyer")
 * - outlet    : daftar dengan kode sales
 * - subdist   : daftar dengan kode distributor (sebelumnya tercatat sebagai "outlet")
 * - distributor
 */
return new class extends Migration
{
    public function up(): void
    {
        if ($this->isMysql()) {
            // Tambah nilai baru dulu (buyer masih ada) supaya data bisa dipindahkan.
            $this->setRoleEnum(array_merge($this->roleValues(), ['ecom', 'subdist']), 'buyer');
        }

        // Subdist: outlet yang sales_code-nya adalah kode milik distributor (termasuk distributor yang sudah dihapus lunak).
        // Kode diambil dulu: MySQL tidak mengizinkan subquery ke tabel yang sedang di-update.
        $distributorCodes = DB::table('users')
            ->where('role', 'distributor')
            ->whereNotNull('distributor_sales_code')
            ->where('distributor_sales_code', '!=', '')
            ->pluck('distributor_sales_code')
            ->all();

        foreach (array_chunk($distributorCodes, 500) as $codes) {
            DB::table('users')
                ->where('role', 'outlet')
                ->whereIn('sales_code', $codes)
                ->update(['role' => 'subdist']);
        }

        DB::table('users')->where('role', 'buyer')->update(['role' => 'ecom']);

        if (Schema::hasTable('user_level_changes')) {
            DB::table('user_level_changes')->where('from_level', 'buyer')->update(['from_level' => 'ecom']);
            DB::table('user_level_changes')->where('to_level', 'buyer')->update(['to_level' => 'ecom']);
        }

        if ($this->isMysql()) {
            $this->setRoleEnum(array_values(array_diff($this->roleValues(), ['buyer'])), 'ecom');
        }
    }

    public function down(): void
    {
        if ($this->isMysql()) {
            $this->setRoleEnum(array_merge($this->roleValues(), ['buyer']), 'ecom');
        }

        DB::table('users')->where('role', 'subdist')->update(['role' => 'outlet']);
        DB::table('users')->where('role', 'ecom')->update(['role' => 'buyer']);

        if (Schema::hasTable('user_level_changes')) {
            DB::table('user_level_changes')->where('from_level', 'ecom')->update(['from_level' => 'buyer']);
            DB::table('user_level_changes')->where('to_level', 'ecom')->update(['to_level' => 'buyer']);
        }

        if ($this->isMysql()) {
            $this->setRoleEnum(array_values(array_diff($this->roleValues(), ['ecom', 'subdist'])), 'buyer');
        }
    }

    private function isMysql(): bool
    {
        return in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true);
    }

    /** Nilai enum role yang ada sekarang, dibaca dari database agar tidak bergantung pada migrasi lama. */
    private function roleValues(): array
    {
        $type = DB::selectOne("SHOW COLUMNS FROM `users` WHERE Field = 'role'")->Type;
        preg_match_all("/'((?:[^']|'')*)'/", $type, $matches);

        return array_values(array_unique(array_map(fn ($v) => str_replace("''", "'", $v), $matches[1])));
    }

    private function setRoleEnum(array $values, string $default): void
    {
        $list = implode(',', array_map(fn ($v) => DB::getPdo()->quote($v), array_values(array_unique($values))));
        DB::statement("ALTER TABLE `users` MODIFY COLUMN `role` ENUM({$list}) DEFAULT " . DB::getPdo()->quote($default));
    }
};
