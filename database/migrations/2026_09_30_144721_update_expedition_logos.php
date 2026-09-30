<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \Illuminate\Support\Facades\DB::table('expeditions')->where('code', 'kurir_toko')->update(['logo' => 'RASA Connect Icon - Delivery.png']);
        \Illuminate\Support\Facades\DB::table('expeditions')->where('code', 'self_pickup')->update(['logo' => 'RASA Connect Icon - Pickup.png']);
        \Illuminate\Support\Facades\DB::table('expeditions')->where('code', 'sicepat')->update(['logo' => 'https://courier.rasaconnect.com/images/carriers/sicepat.png']);
        \Illuminate\Support\Facades\DB::table('expeditions')->where('code', 'jne')->update(['logo' => 'https://courier.rasaconnect.com/images/carriers/jne.png']);
        \Illuminate\Support\Facades\DB::table('expeditions')->where('code', 'jnt')->update(['logo' => 'https://courier.rasaconnect.com/images/carriers/jnt.png']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
