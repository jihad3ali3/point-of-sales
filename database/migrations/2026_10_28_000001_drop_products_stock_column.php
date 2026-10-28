<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Makes `product_warehouse` the single source of truth for stock.
 *
 * Upgrading an existing install:
 *   1. any product whose legacy `products.stock` has no pivot row yet gets one
 *      seeded into the central PUSAT warehouse, so no stock is lost;
 *   2. the legacy `products.stock` column is dropped.
 *
 * A fresh install never creates the column, so step 1 and 2 are no-ops.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('products', 'stock')) {
            return;
        }

        $pusatId = DB::table('warehouses')->where('code', 'PUSAT')->value('id');

        if ($pusatId !== null) {
            DB::table('products')
                ->where('stock', '>', 0)
                ->whereNotExists(fn ($query) => $query->selectRaw('1')
                    ->from('product_warehouse')
                    ->whereColumn('product_warehouse.product_id', 'products.id'))
                ->orderBy('id')
                ->each(function ($product) use ($pusatId) {
                    DB::table('product_warehouse')->insert([
                        'product_id' => $product->id,
                        'warehouse_id' => $pusatId,
                        'stock' => (int) $product->stock,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });
        }

        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('stock');
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('products', 'stock')) {
            return;
        }

        Schema::table('products', function (Blueprint $table) {
            $table->integer('stock')->default(0)->after('sell_price');
        });

        DB::table('products')->orderBy('id')->each(function ($product) {
            DB::table('products')
                ->where('id', $product->id)
                ->update([
                    'stock' => (int) DB::table('product_warehouse')
                        ->where('product_id', $product->id)
                        ->sum('stock'),
                ]);
        });
    }
};
