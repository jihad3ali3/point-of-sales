<?php

namespace Database\Factories;

use App\Models\Outlet;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Every user belongs to at least one outlet. The factory joins the default
     * sales outlet when the fixture already provides one, so tests never end up
     * with an account the strict access layer rejects.
     */
    public function configure(): static
    {
        return $this->afterCreating(function (User $user) {
            if ($user->outlets()->exists()) {
                return;
            }

            $outlet = Outlet::query()
                ->where('is_active', true)
                ->orderByDesc('is_sales_enabled')
                ->orderBy('code')
                ->first();

            if ($outlet) {
                $user->outlets()->syncWithoutDetaching([$outlet->id => ['is_default' => true]]);
            }
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
