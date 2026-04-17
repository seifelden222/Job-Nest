<?php

namespace Database\Factories;

use App\Models\PersonProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PersonProfile>
 */
class PersonProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory()->person(),
            'university' => fake()->company(),
            'major' => fake()->word(),
            'employment_status' => 'employed',
            'employment_type' => 'full_time',
            'current_job_title' => fake()->jobTitle(),
            'company_name' => fake()->company(),
            'preferred_work_location' => 'hybrid',
            'expected_salary_min' => 3000,
            'expected_salary_max' => 8000,
            'linkedin_url' => null,
            'portfolio_url' => null,
            'about' => fake()->paragraph(),
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
