<?php

namespace Database\Factories;

use App\Models\Outlet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Outlet>
 */
class OutletFactory extends Factory
{
    protected $model = Outlet::class;

    public function definition(): array
    {
        return [
            'code' => strtoupper(fake()->unique()->lexify('OUT-???')),
            'name' => fake()->company(),
            'is_active' => true,
            'is_sales_enabled' => true,
            'address' => fake()->address(),
            'phone' => fake()->phoneNumber(),
        ];
    }

    /**
     * A central outlet that can hold stock but cannot sell.
     */
    public function central(): static
    {
        return $this->state(fn () => [
            'code' => 'PUSAT',
            'name' => 'Pusat',
            'is_sales_enabled' => false,
        ]);
    }
}
