<?php

namespace App\Services;

use App\Models\CashierShift;
use App\Models\Outlet;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class OutletAccessService
{
    /**
     * A warehouse is usable only when it is active, belongs to an active outlet,
     * and that outlet is assigned to the user. There is no single-outlet bypass:
     * every user must be assigned through `user_outlets`.
     */
    public function canUseWarehouse(User $user, ?Warehouse $warehouse): bool
    {
        if (! $warehouse || ! $warehouse->is_active || ! $warehouse->outlet_id) {
            return false;
        }

        if ($user->isSuperAdmin()) {
            return true;
        }

        $outlet = $warehouse->relationLoaded('outlet')
            ? $warehouse->outlet
            : Outlet::find($warehouse->outlet_id);

        if (! $outlet || ! $outlet->is_active) {
            return false;
        }

        return $user->outlets()
            ->where('outlets.is_active', true)
            ->where('outlets.id', $warehouse->outlet_id)
            ->exists();
    }

    /**
     * Selling requires a usable warehouse whose outlet is sales-enabled.
     * `PUSAT` (or any non-sales outlet) can never host a sale or a shift.
     */
    public function canSellAtWarehouse(User $user, ?Warehouse $warehouse): bool
    {
        if (! $this->canUseWarehouse($user, $warehouse)) {
            return false;
        }

        $outlet = $warehouse->relationLoaded('outlet')
            ? $warehouse->outlet
            : Outlet::find($warehouse->outlet_id);

        return (bool) $outlet?->is_sales_enabled;
    }

    public function salesWarehousesFor(User $user): Collection
    {
        return $this->warehousesFor($user)
            ->filter(fn (Warehouse $warehouse) => (bool) $warehouse->outlet?->is_sales_enabled)
            ->values();
    }

    public function warehousesFor(User $user): Collection
    {
        $query = Warehouse::query()
            ->active()
            ->whereNotNull('outlet_id')
            ->orderBy('sort_order')
            ->orderBy('code');

        if (! $user->isSuperAdmin()) {
            $outletIds = $this->accessibleOutlets($user)->pluck('id');

            if ($outletIds->isEmpty()) {
                return new Collection;
            }

            $query->whereIn('outlet_id', $outletIds);
        }

        return $query->with('outlet:id,code,is_sales_enabled,is_active')->get([
            'id',
            'code',
            'name',
            'outlet_id',
            'is_active',
            'type',
        ]);
    }

    public function defaultOutlet(User $user): ?Outlet
    {
        if ($user->isSuperAdmin()) {
            return Outlet::active()->orderBy('code')->first();
        }

        return $user->outlets()
            ->where('outlets.is_active', true)
            ->orderByDesc('user_outlets.is_default')
            ->orderBy('outlets.code')
            ->first();
    }

    public function accessibleOutlets(User $user): Collection
    {
        if ($user->isSuperAdmin()) {
            return Outlet::active()->orderBy('code')->get();
        }

        return $user->outlets()
            ->where('outlets.is_active', true)
            ->orderByDesc('user_outlets.is_default')
            ->orderBy('outlets.code')
            ->get();
    }

    public function activeOutlet(Request $request): ?Outlet
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        $shiftOutlet = CashierShift::query()
            ->with('warehouse.outlet')
            ->open()
            ->where('user_id', $user->id)
            ->latest('opened_at')
            ->first()?->warehouse?->outlet;

        if ($shiftOutlet) {
            return $shiftOutlet;
        }

        $outlets = $this->accessibleOutlets($user);

        if ($outlets->isEmpty()) {
            return null;
        }

        $selectedId = (int) $request->session()->get('active_outlet_id');

        return $outlets->firstWhere('id', $selectedId) ?? $outlets->first();
    }

    public function hasActiveShiftInOtherOutlet(User $user, Outlet $outlet): bool
    {
        return (bool) CashierShift::query()
            ->where('user_id', $user->id)
            ->open()
            ->whereHas('warehouse', fn ($query) => $query->where('outlet_id', '!=', $outlet->id))
            ->exists();
    }

    public function hasWarehouseHistory(Warehouse $warehouse): bool
    {
        $warehouseId = $warehouse->id;

        foreach ([
            'transactions',
            'carts',
            'stock_mutations',
            'cashier_shifts',
            'purchase_orders',
            'goods_receivings',
            'supplier_returns',
            'stock_opnames',
            'product_batches',
        ] as $table) {
            if (DB::table($table)->where('warehouse_id', $warehouseId)->exists()) {
                return true;
            }
        }

        return DB::table('stock_transfers')->where('source_warehouse_id', $warehouseId)->exists()
            || DB::table('stock_transfers')->where('destination_warehouse_id', $warehouseId)->exists();
    }

    public function hasOpenShift(Warehouse $warehouse): bool
    {
        return Schema::hasTable('cashier_shifts')
            && DB::table('cashier_shifts')
                ->where('warehouse_id', $warehouse->id)
                ->where('status', 'open')
                ->exists();
    }
}
