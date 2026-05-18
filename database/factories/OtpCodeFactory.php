<?php

namespace Database\Factories;

use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OtpCode>
 */
class OtpCodeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_type' => 'user',
            'user_id' => User::factory(),
            'email' => fn (array $attributes): ?string => User::query()->find($attributes['user_id'])?->email,
            'phone' => fn (array $attributes): ?string => fake()->boolean(35)
                ? User::query()->find($attributes['user_id'])?->phone
                : null,
            'code' => (string) random_int(100000, 999999),
            'type' => fake()->randomElement(['verify_email', 'reset_password']),
            'expires_at' => now()->addMinutes(fake()->numberBetween(10, 30)),
            'verified_at' => null,
        ];
    }

    public function expired(): static
    {
        return $this->state(fn (): array => [
            'expires_at' => now()->subMinutes(fake()->numberBetween(5, 50)),
        ]);
    }

    public function verified(): static
    {
        return $this->state(fn (): array => [
            'verified_at' => now()->subMinutes(fake()->numberBetween(1, 15)),
        ]);
    }
}
