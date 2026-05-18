<?php

namespace Database\Factories;

use App\Models\PortfolioItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PortfolioItem>
 */
class PortfolioItemFactory extends Factory
{
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('-3 years', '-1 month');
        $endDate = fake()->dateTimeBetween($startDate, 'now');

        return [
            'user_id' => User::factory()->person(),
            'title' => fake()->sentence(4),
            'description' => fake()->paragraph(),
            'live_url' => fake()->optional()->url(),
            'image_path' => null,
            'started_at' => $startDate->format('Y-m-d'),
            'completed_at' => $endDate->format('Y-m-d'),
            'status' => fake()->randomElement(['in_progress', 'completed', 'archived']),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'completed',
        ]);
    }

    public function inProgress(): static
    {
        return $this->state(fn (): array => [
            'status' => 'in_progress',
            'completed_at' => null,
        ]);
    }
}
