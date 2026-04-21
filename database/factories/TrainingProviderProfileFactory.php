<?php

namespace Database\Factories;

use App\Models\TrainingProviderProfile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TrainingProviderProfile>
 */
class TrainingProviderProfileFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'provider_name' => fake()->company(),
            'website' => fake()->url(),
            'industry' => fake()->word(),
            'location' => fake()->city(),
            'about' => fake()->paragraph(),
            'logo' => null,
            'is_verified' => false,
            'onboarding_step' => 1,
            'is_profile_completed' => false,
        ];
    }
}
