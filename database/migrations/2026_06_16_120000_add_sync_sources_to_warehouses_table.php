<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->json('sync_sources')->nullable()->after('description');
        });

        DB::table('warehouses')->orderBy('id')->chunk(100, function ($warehouses) {
            foreach ($warehouses as $warehouse) {
                $sources = [];
                $description = (string) ($warehouse->description ?? '');

                if (stripos($description, 'jubelio') !== false) {
                    $sources[] = 'jubelio';
                }
                if (stripos($description, 'qad') !== false) {
                    $sources[] = 'qad';
                }

                if ($sources !== []) {
                    DB::table('warehouses')
                        ->where('id', $warehouse->id)
                        ->update(['sync_sources' => json_encode(array_values(array_unique($sources)))]);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('warehouses', function (Blueprint $table) {
            $table->dropColumn('sync_sources');
        });
    }
};
