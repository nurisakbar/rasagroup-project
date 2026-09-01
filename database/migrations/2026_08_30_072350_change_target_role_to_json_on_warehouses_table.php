<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Data migration: ensure existing target_role values are JSON strings
        DB::table('warehouses')->get()->each(function ($warehouse) {
            $role = $warehouse->target_role;
            if ($role && !str_starts_with($role, '[')) {
                DB::table('warehouses')
                    ->where('id', $warehouse->id)
                    ->update(['target_role' => json_encode([$role])]);
            }
        });
    }

    public function down(): void
    {
        // Data migration: convert JSON array back to single string
        DB::table('warehouses')->get()->each(function ($warehouse) {
            $role = $warehouse->target_role;
            if ($role && str_starts_with($role, '[')) {
                $decoded = json_decode($role, true);
                $firstRole = is_array($decoded) && count($decoded) > 0 ? $decoded[0] : 'umum';
                DB::table('warehouses')
                    ->where('id', $warehouse->id)
                    ->update(['target_role' => $firstRole]);
            }
        });
    }
};
