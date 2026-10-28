<?php

namespace Database\Seeders;

use App\Models\Outlet;
use App\Models\Setting;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\PermissionRegistrar;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->call([
            PermissionSeeder::class,
            RoleSeeder::class,
            PaymentSettingSeeder::class,
            DineInSettingsSeeder::class,
        ]);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $this->seedDefaultWarehouse();
    }

    private function seedDefaultWarehouse(): void
    {
        $pusat = Warehouse::firstOrCreate(
            ['code' => 'PUSAT'],
            [
                'name' => 'Gudang Pusat',
                'type' => 'main',
                'is_active' => true,
                'sort_order' => 0,
            ],
        );

        $outlet = Outlet::firstOrCreate(
            ['code' => 'PUSAT'],
            [
                'name' => $pusat->name,
                'is_active' => true,
                'is_sales_enabled' => false,
                'address' => $pusat->address,
                'phone' => $pusat->phone,
            ],
        );

        if (! $pusat->outlet_id) {
            $pusat->update(['outlet_id' => $outlet->id]);
        }

        // Every account must belong to an outlet; the central outlet is the
        // default for users created before any branch exists.
        DB::table('users')
            ->whereNotExists(fn ($query) => $query->selectRaw('1')
                ->from('user_outlets')
                ->whereColumn('user_outlets.user_id', 'users.id'))
            ->pluck('id')
            ->each(fn ($userId) => DB::table('user_outlets')->insertOrIgnore([
                'user_id' => $userId,
                'outlet_id' => $outlet->id,
                'is_default' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));

        if (! Setting::get('setup_warehouse_id')) {
            Setting::set('setup_warehouse_id', $pusat->id);
        }

        $products = DB::table('products')
            ->whereNotExists(fn ($query) => $query->selectRaw('1')
                ->from('product_warehouse')
                ->whereColumn('product_warehouse.product_id', 'products.id'))
            ->pluck('id')
            ->map(fn ($productId) => [
                'product_id' => $productId,
                'warehouse_id' => $pusat->id,
                'stock' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->all();

        if ($products) {
            DB::table('product_warehouse')->insertOrIgnore($products);
        }
    }
}
