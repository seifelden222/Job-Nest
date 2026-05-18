<?php

namespace Database\Factories;

use App\Models\Admin;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<Admin>
 */
class AdminFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->numerify('01#########'),
            'password' => static::$password ??= Hash::make('password'),
            'profile_photo' => null,
            'status' => 'active',
            'last_login_at' => fake()->boolean(70) ? now()->subDays(fake()->numberBetween(0, 14)) : null,
            'remember_token' => Str::random(10),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => 'inactive',
            'last_login_at' => now()->subMonths(fake()->numberBetween(1, 4)),
        ]);
    }
}
