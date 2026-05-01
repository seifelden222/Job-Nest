<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory()->state(['type' => 'service']),
            'title' => ['en' => fake()->sentence(4), 'ar' => fake()->sentence(4)],
            'description' => ['en' => fake()->paragraph(), 'ar' => fake()->paragraph()],
            'budget_min' => 1000,
            'budget_max' => 3000,
            'currency' => 'EGP',
            'location' => fake()->city(),
            'delivery_mode' => fake()->randomElement(['online', 'offline', 'hybrid']),
            'deadline' => now()->addWeeks(2)->toDateString(),
            'status' => 'open',
        ];
    }
}
