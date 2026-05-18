<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->unique()->numerify('01#########'),
            'password' => static::$password ??= Hash::make('password'),
            'account_type' => 'person',
            'profile_photo' => null,
            'status' => 'active',
            'email_verified_at' => now()->subDays(fake()->numberBetween(1, 180)),
            'remember_token' => Str::random(10),
        ];
    }

    public function person(): static
    {
        return $this->state(fn (): array => [
            'account_type' => 'person',
        ]);
    }

    public function company(): static
    {
        return $this->state(fn (): array => [
            'account_type' => 'company',
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => [
            'status' => 'inactive',
        ]);
    }

    public function suspended(): static
    {
        return $this->state(fn (): array => [
            'status' => 'suspended',
        ]);
    }

    public function unverified(): static
    {
        return $this->state(fn (): array => [
            'email_verified_at' => null,
        ]);
    }
}
