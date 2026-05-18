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
        $persona = fake()->randomElement([
            [
                'title' => 'Junior Laravel Developer',
                'major' => 'Computer Science',
                'university' => 'Cairo University',
                'company_name' => 'Freelance Projects',
                'employment_status' => 'seeking_opportunities',
                'employment_type' => 'full_time',
                'preferred_work_location' => 'hybrid',
                'salary_range' => [12000, 18000],
            ],
            [
                'title' => 'Flutter Developer',
                'major' => 'Software Engineering',
                'university' => 'Ain Shams University',
                'company_name' => 'Nile Commerce',
                'employment_status' => 'employed',
                'employment_type' => 'full_time',
                'preferred_work_location' => 'remote',
                'salary_range' => [16000, 24000],
            ],
            [
                'title' => 'Product Designer',
                'major' => 'Graphic Design',
                'university' => 'German University in Cairo',
                'company_name' => 'Independent Consultant',
                'employment_status' => 'freelancer',
                'employment_type' => 'contract',
                'preferred_work_location' => 'remote',
                'salary_range' => [15000, 23000],
            ],
            [
                'title' => 'Data Analyst',
                'major' => 'Information Systems',
                'university' => 'Alexandria University',
                'company_name' => 'Cedar Health',
                'employment_status' => 'employed',
                'employment_type' => 'full_time',
                'preferred_work_location' => 'onsite',
                'salary_range' => [14000, 22000],
            ],
            [
                'title' => 'Digital Marketing Specialist',
                'major' => 'Marketing',
                'university' => 'Mansoura University',
                'company_name' => 'Growth Hub Egypt',
                'employment_status' => 'seeking_opportunities',
                'employment_type' => 'part_time',
                'preferred_work_location' => 'hybrid',
                'salary_range' => [10000, 18000],
            ],
            [
                'title' => 'Undergraduate Student',
                'major' => 'Computer Engineering',
                'university' => 'Helwan University',
                'company_name' => null,
                'employment_status' => 'student',
                'employment_type' => 'internship',
                'preferred_work_location' => 'hybrid',
                'salary_range' => [6000, 10000],
            ],
        ]);

        return [
            'user_id' => User::factory()->person(),
            'university' => $persona['university'],
            'major' => $persona['major'],
            'employment_status' => $persona['employment_status'],
            'employment_type' => $persona['employment_type'],
            'current_job_title' => $persona['title'],
            'company_name' => $persona['company_name'],
            'linkedin_url' => fake()->boolean(85) ? fake()->url() : null,
            'portfolio_url' => in_array($persona['title'], ['Junior Laravel Developer', 'Flutter Developer', 'Product Designer'], true)
                ? fake()->url()
                : null,
            'preferred_work_location' => $persona['preferred_work_location'],
            'expected_salary_min' => $persona['salary_range'][0],
            'expected_salary_max' => $persona['salary_range'][1],
            'about' => fake()->randomElement([
                'Hands-on professional focused on building dependable digital products, collaborating well with cross-functional teams, and continuously improving delivery quality.',
                'Results-driven candidate with practical project experience, strong ownership, and a clear interest in solving business problems through technology.',
                'Detail-oriented profile combining technical execution with communication skills, stakeholder empathy, and a strong desire to grow in fast-moving teams.',
            ]),
            'onboarding_step' => fake()->randomElement([2, 3]),
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
