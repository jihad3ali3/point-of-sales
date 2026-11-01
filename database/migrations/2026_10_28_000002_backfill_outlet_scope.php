<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Repairs data written before the outlet boundary became mandatory.
 *
 * The application no longer contains any fallback for warehouse-less or
 * outlet-less records, so an upgraded install must have its legacy rows
 * attached before the strict code runs. This migration is idempotent and
 * never deletes anything.
 *
 * A fresh install has nothing to repair: every step is a no-op.
 */
return new class extends Migration
{
    /**
     * Tables whose `warehouse_id` must resolve to a real warehouse.
     */
    private const WAREHOUSE_TABLES = [
        'transactions',
        'carts',
        'stock_mutations',
        'cashier_shifts',
        'purchase_orders',
        'goods_receivings',
        'supplier_returns',
        'stock_opnames',
        'sales_returns',
    ];

    public function up(): void
    {
        $pusatOutletId = $this->resolvePusatOutlet();
        $pusatWarehouseId = $this->resolvePusatWarehouse($pusatOutletId);

        if ($pusatOutletId !== null) {
            $this->assignUnassignedUsers($pusatOutletId);
            $this->adoptOrphanWarehouses($pusatOutletId);
        }

        if ($pusatWarehouseId !== null) {
            $this->backfillWarehouseColumns($pusatWarehouseId);
        }
    }

    public function down(): void
    {
        // Data repair is not reversible: nothing is removed, only filled in.
    }

    private function resolvePusatOutlet(): ?int
    {
        if (! Schema::hasTable('outlets')) {
            return null;
        }

        $id = DB::table('outlets')->where('code', 'PUSAT')->value('id');

        if ($id === null) {
            $id = DB::table('outlets')->insertGetId([
                'code' => 'PUSAT',
                'name' => 'Pusat',
                'is_active' => true,
                'is_sales_enabled' => false,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        return (int) $id;
    }

    private function resolvePusatWarehouse(?int $pusatOutletId): ?int
    {
        if (! Schema::hasTable('warehouses')) {
            return null;
        }

        $id = DB::table('warehouses')->where('code', 'PUSAT')->value('id');

        if ($id === null) {
            $id = DB::table('warehouses')->insertGetId([
                'outlet_id' => $pusatOutletId,
                'code' => 'PUSAT',
                'name' => 'Gudang Pusat',
                'type' => 'main',
                'is_active' => true,
                'sort_order' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($id !== null && $pusatOutletId !== null) {
            DB::table('warehouses')
                ->where('id', $id)
                ->whereNull('outlet_id')
                ->update(['outlet_id' => $pusatOutletId, 'updated_at' => now()]);
        }

        return $id === null ? null : (int) $id;
    }

    /**
     * Every user must belong to at least one outlet; the central outlet is the
     * safe default for accounts created before outlets existed.
     */
    private function assignUnassignedUsers(int $pusatOutletId): void
    {
        if (! Schema::hasTable('user_outlets')) {
            return;
        }

        $userIds = DB::table('users')
            ->whereNotExists(fn ($query) => $query->selectRaw('1')
                ->from('user_outlets')
                ->whereColumn('user_outlets.user_id', 'users.id'))
            ->pluck('id');

        foreach ($userIds as $userId) {
            DB::table('user_outlets')->insertOrIgnore([
                'user_id' => $userId,
                'outlet_id' => $pusatOutletId,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Warehouses created before the outlet table existed get adopted by the
     * central outlet so they stay reachable.
     */
    private function adoptOrphanWarehouses(int $pusatOutletId): void
    {
        DB::table('warehouses')
            ->whereNull('outlet_id')
            ->update(['outlet_id' => $pusatOutletId, 'updated_at' => now()]);
    }

    private function backfillWarehouseColumns(int $pusatWarehouseId): void
    {
        foreach (self::WAREHOUSE_TABLES as $table) {
            if (! Schema::hasTable($table) || ! Schema::hasColumn($table, 'warehouse_id')) {
                continue;
            }

            DB::table($table)
                ->whereNull('warehouse_id')
                ->update(['warehouse_id' => $pusatWarehouseId, 'updated_at' => now()]);
        }

        if (Schema::hasTable('stock_transfers')) {
            foreach (['source_warehouse_id', 'destination_warehouse_id'] as $column) {
                if (Schema::hasColumn('stock_transfers', $column)) {
                    DB::table('stock_transfers')
                        ->whereNull($column)
                        ->update([$column => $pusatWarehouseId, 'updated_at' => now()]);
                }
            }
        }
    }
};
