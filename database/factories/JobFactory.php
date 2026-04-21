<?php

namespace Database\Factories;

use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Job>
 */
class JobFactory extends Factory
{
    public function definition(): array
    {
        return [
            'company_id' => User::factory()->company(),
            'title' => fake()->jobTitle(),
            'description' => fake()->paragraphs(2, true),
            'location' => fake()->city(),
            'employment_type' => fake()->randomElement(['full_time', 'part_time', 'contract']),
            'salary_min' => 3000,
            'salary_max' => 6000,
            'currency' => 'EGP',
            'experience_level' => fake()->randomElement(['junior', 'mid', 'senior']),
            'requirements' => fake()->sentence(),
            'responsibilities' => fake()->sentence(),
            'deadline' => now()->addWeeks(2)->toDateString(),
            'status' => 'active',
            'is_active' => true,
            'applications_count' => 0,
        ];
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => 'draft',
            'is_active' => false,
        ]);
    }
}
