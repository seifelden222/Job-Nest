<?php

namespace Database\Factories;

use App\Models\RefreshToken;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<RefreshToken>
 */
class RefreshTokenFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'access_token_id' => null,
            'family_id' => (string) Str::uuid(),
            'replaced_by_token_id' => null,
            'name' => fake()->randomElement(['iPhone 15 Pro', 'MacBook Pro', 'Flutter Test Device', 'Chrome Browser']),
            'token_hash' => hash('sha256', (string) Str::uuid()),
            'ip_address' => fake()->ipv4(),
            'user_agent' => fake()->userAgent(),
            'last_used_at' => now()->subDays(fake()->numberBetween(0, 10)),
            'revoked_at' => null,
            'expires_at' => now()->addDays(fake()->numberBetween(15, 45)),
        ];
    }

    public function revoked(): static
    {
        return $this->state(fn (): array => [
            'revoked_at' => now()->subDays(fake()->numberBetween(1, 10)),
        ]);
    }
}
