<?php

use App\Support\AdminAccess\DefaultModules;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Aturan bonus pembelian: beli X dapat hadiah (produk tetap, atau pilih bebas dari daftar).
        Schema::create('bonus_rules', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name', 150);
            $table->string('type', 20);                       // fixed_gift | free_choice
            $table->unsignedInteger('buy_qty');               // syarat beli
            $table->unsignedInteger('gift_qty');              // jumlah hadiah
            $table->string('unit', 10)->default('carton');    // carton | pcs (berlaku untuk syarat dan hadiah)
            $table->boolean('is_multiple')->default(true);    // berlaku kelipatan
            $table->unsignedInteger('max_gift_qty')->nullable();
            $table->json('customer_types');                   // ecom, outlet, subdist, distributor
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bonus_rule_buy_products', function (Blueprint $table) {
            $table->id();
            $table->uuid('bonus_rule_id');
            $table->uuid('product_id');
            $table->foreign('bonus_rule_id')->references('id')->on('bonus_rules')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->unique(['bonus_rule_id', 'product_id']);
        });

        Schema::create('bonus_rule_gift_products', function (Blueprint $table) {
            $table->id();
            $table->uuid('bonus_rule_id');
            $table->uuid('product_id');
            $table->foreign('bonus_rule_id')->references('id')->on('bonus_rules')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->unique(['bonus_rule_id', 'product_id']);
        });

        // Pilihan hadiah pembeli untuk aturan bertipe free_choice.
        Schema::create('bonus_choices', function (Blueprint $table) {
            $table->id();
            $table->uuid('user_id');
            $table->uuid('bonus_rule_id');
            $table->uuid('product_id');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('bonus_rule_id')->references('id')->on('bonus_rules')->cascadeOnDelete();
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->unique(['user_id', 'bonus_rule_id']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->boolean('is_bonus')->default(false)->after('subtotal');
            $table->uuid('bonus_rule_id')->nullable()->after('is_bonus');
        });

        DefaultModules::seed();
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropColumn(['is_bonus', 'bonus_rule_id']);
        });
        Schema::dropIfExists('bonus_choices');
        Schema::dropIfExists('bonus_rule_gift_products');
        Schema::dropIfExists('bonus_rule_buy_products');
        Schema::dropIfExists('bonus_rules');
    }
};
