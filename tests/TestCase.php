<?php

namespace Tests;

use App\Models\Outlet;
use App\Models\User;
use App\Models\Warehouse;
use App\Support\BotGuard;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Crypt;

abstract class TestCase extends BaseTestCase
{
    protected function botGuardPayload(): array
    {
        $payload = BotGuard::payload();

        return [
            $payload['honeypot_field'] => '',
            $payload['token_field'] => Crypt::encryptString(json_encode([
                'iat' => now()->subSeconds(max(3, config('security.bot_guard.min_submit_seconds', 2) + 1))->timestamp,
                'nonce' => 'test-nonce',
            ], JSON_THROW_ON_ERROR)),
        ];
    }

    /**
     * The outlet a test fixture should belong to. Reuses the seeded central
     * outlet when present, otherwise creates a sales-enabled one.
     */
    protected function defaultOutlet(bool $salesEnabled = true): Outlet
    {
        return Outlet::query()
            ->where('is_active', true)
            ->where('is_sales_enabled', $salesEnabled)
            ->orderBy('code')
            ->first()
            ?? Outlet::factory()->create(['is_sales_enabled' => $salesEnabled]);
    }

    /**
     * Create a warehouse that satisfies the strict outlet rules.
     */
    protected function makeWarehouse(array $attributes = [], ?Outlet $outlet = null): Warehouse
    {
        return Warehouse::factory()->create([
            'outlet_id' => ($outlet ?? $this->defaultOutlet())->id,
            ...$attributes,
        ]);
    }

    /**
     * Put stock on a warehouse so the POS can actually sell the product.
     */
    protected function stockProductAt(Warehouse $warehouse, \App\Models\Product $product, int $quantity): void
    {
        $product->warehouses()->syncWithoutDetaching([
            $warehouse->id => ['stock' => $quantity],
        ]);
    }

    /**
     * A user assigned to the given outlet (or the default one).
     */
    protected function userInOutlet(?Outlet $outlet = null, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->outlets()->syncWithoutDetaching([
            ($outlet ?? $this->defaultOutlet())->id => ['is_default' => true],
        ]);

        return $user;
    }

    protected function recentlyConfirmedSession(): array
    {
        return [
            'auth.password_confirmed_at' => time(),
        ];
    }
}
