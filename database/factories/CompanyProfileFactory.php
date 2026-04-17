<?php

namespace Database\Factories;

use App\Models\CompanyProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CompanyProfile>
 */
class CompanyProfileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */

    /**
     * @extends Factory<CompanyProfile>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->company(),
            'company_name' => fake()->company(),
            'website' => fake()->url(),
            'company_size' => '51-200',
            'industry' => 'Technology',
            'location' => fake()->city(),
            'about' => fake()->paragraph(),
            'logo' => null,
            'onboarding_step' => 1,
            'is_profile_completed' => false,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'onboarding_step' => 3,
            'is_profile_completed' => true,
        ]);
    }
}
