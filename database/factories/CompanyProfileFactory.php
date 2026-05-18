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
    public function definition(): array
    {
        $company = fake()->randomElement([
            [
                'company_name' => 'Injaz Labs',
                'industry' => 'Technology',
                'location' => 'New Cairo, Egypt',
                'website' => 'https://injazlabs.com',
                'company_size' => '51-200',
            ],
            [
                'company_name' => 'Nile Commerce',
                'industry' => 'E-commerce',
                'location' => 'Cairo, Egypt',
                'website' => 'https://nilecommerce.co',
                'company_size' => '51-200',
            ],
            [
                'company_name' => 'Cedar Health',
                'industry' => 'Healthcare',
                'location' => 'Riyadh, Saudi Arabia',
                'website' => 'https://cedarhealth.io',
                'company_size' => '11-50',
            ],
            [
                'company_name' => 'Atlas Logistics',
                'industry' => 'Logistics',
                'location' => 'Alexandria, Egypt',
                'website' => 'https://atlaslogistics.co',
                'company_size' => '201-500',
            ],
            [
                'company_name' => 'Riwaq Education',
                'industry' => 'Education',
                'location' => 'Dubai, UAE',
                'website' => 'https://riwaqeducation.com',
                'company_size' => '11-50',
            ],
            [
                'company_name' => 'Sprints Studio',
                'industry' => 'Creative Services',
                'location' => 'Remote - MENA',
                'website' => 'https://sprintsstudio.design',
                'company_size' => '1-10',
            ],
        ]);

        return [
            'user_id' => User::factory()->company(),
            'company_name' => $company['company_name'],
            'website' => $company['website'],
            'company_size' => $company['company_size'],
            'industry' => $company['industry'],
            'location' => $company['location'],
            'about' => fake()->randomElement([
                'Growth-focused company building practical digital products and services for businesses across Egypt and the wider MENA region.',
                'Team known for combining strong execution, client partnership, and measurable operational impact across product, engineering, and go-to-market work.',
                'Employer with a collaborative culture, clear ownership, and an emphasis on delivering reliable customer experiences at scale.',
            ]),
            'logo' => null,
            'onboarding_step' => 3,
            'is_profile_completed' => true,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (): array => [
            'onboarding_step' => 3,
            'is_profile_completed' => true,
        ]);
    }
}
