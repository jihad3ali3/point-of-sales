<?php

namespace Database\Factories;

use App\Models\Outlet;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;

    public function definition(): array
    {
        return [
            'outlet_id' => fn () => $this->salesOutletId(),
            'code' => strtoupper(fake()->unique()->lexify('WH-???')),
            'name' => fake()->company(),
            'type' => 'branch',
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * A warehouse is always owned by an outlet. Tests that build a sellable
     * warehouse get a sales-enabled outlet; one is created on demand when the
     * fixture did not provide any.
     */
    private function salesOutletId(): int
    {
        $existing = Outlet::query()
            ->where('is_active', true)
            ->where('is_sales_enabled', true)
            ->orderBy('code')
            ->value('id');

        return $existing ?? Outlet::factory()->create()->id;
    }
}
