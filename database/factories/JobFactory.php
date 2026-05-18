<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Job;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Job>
 */
class JobFactory extends Factory
{
    public function definition(): array
    {
        $role = fake()->randomElement([
            [
                'industry' => 'Technology',
                'title_en' => 'Senior Laravel Backend Engineer',
                'title_ar' => 'مهندس باك إند لارافيل أول',
                'experience' => 'senior',
                'salary' => [28000, 42000],
            ],
            [
                'industry' => 'Technology',
                'title_en' => 'Flutter Mobile Developer',
                'title_ar' => 'مطوّر تطبيقات Flutter',
                'experience' => 'mid',
                'salary' => [18000, 28000],
            ],
            [
                'industry' => 'E-commerce',
                'title_en' => 'Performance Marketing Specialist',
                'title_ar' => 'أخصائي تسويق رقمي للأداء',
                'experience' => 'mid',
                'salary' => [15000, 24000],
            ],
            [
                'industry' => 'Design',
                'title_en' => 'UI/UX Product Designer',
                'title_ar' => 'مصمم منتجات UI/UX',
                'experience' => 'mid',
                'salary' => [14000, 23000],
            ],
            [
                'industry' => 'Data',
                'title_en' => 'Business Intelligence Analyst',
                'title_ar' => 'محلل ذكاء أعمال',
                'experience' => 'junior',
                'salary' => [12000, 18000],
            ],
            [
                'industry' => 'Operations',
                'title_en' => 'Operations Coordinator',
                'title_ar' => 'منسق عمليات',
                'experience' => 'junior',
                'salary' => [9000, 14000],
            ],
            [
                'industry' => 'Technology',
                'title_en' => 'Product Manager',
                'title_ar' => 'مدير منتج',
                'experience' => 'mid',
                'salary' => [22000, 34000],
            ],
            [
                'industry' => 'Customer Experience',
                'title_en' => 'Customer Success Specialist',
                'title_ar' => 'أخصائي نجاح العملاء',
                'experience' => 'junior',
                'salary' => [10000, 16000],
            ],
        ]);
        $salaryMin = $role['salary'][0];
        $salaryMax = $role['salary'][1];

        return [
            'company_id' => User::factory()->company(),
            'category_id' => Category::factory()->state(['type' => 'job']),
            'industry' => $role['industry'],
            'title' => [
                'en' => $role['title_en'],
                'ar' => $role['title_ar'],
            ],
            'description' => [
                'en' => fake()->paragraphs(3, true),
                'ar' => 'هذه فرصة مميزة للانضمام إلى فريق متنامٍ والعمل على مشاريع حقيقية ذات أثر واضح على العملاء والأعمال.',
            ],
            'location' => fake()->randomElement([
                'Cairo, Egypt',
                'New Cairo, Egypt',
                'Alexandria, Egypt',
                'Mansoura, Egypt',
                'Giza, Egypt',
                'Riyadh, Saudi Arabia',
                'Remote - MENA',
                'Dubai, UAE',
            ]),
            'employment_type' => fake()->randomElement(['full_time', 'full_time', 'part_time', 'contract']),
            'salary_min' => $salaryMin,
            'salary_max' => $salaryMax,
            'currency' => fake()->randomElement(['EGP', 'SAR', 'USD']),
            'experience_level' => $role['experience'],
            'requirements' => [
                'en' => fake()->randomElement([
                    'Proven practical experience in a similar role, strong communication skills, and the ability to collaborate closely with product and operations teams.',
                    'Solid understanding of modern tooling, ownership mindset, and a track record of delivering maintainable work on real business problems.',
                    'Ability to prioritize well, communicate clearly, and contribute to continuous improvement across quality, speed, and user experience.',
                ]),
                'ar' => 'خبرة عملية مثبتة، تواصل قوي، والقدرة على العمل ضمن فريق وتحقيق النتائج ضمن المواعيد المحددة.',
            ],
            'responsibilities' => [
                'en' => fake()->randomElement([
                    'Own day-to-day execution, coordinate with cross-functional stakeholders, and improve delivery quality for core platform initiatives.',
                    'Build and iterate on customer-facing features, support internal teams, and contribute to scalable operational processes.',
                    'Translate business goals into dependable execution while maintaining strong documentation, teamwork, and follow-through.',
                ]),
                'ar' => 'قيادة المهام اليومية، التنسيق مع الفرق المختلفة، وتحسين جودة التنفيذ وتجربة المستخدم.',
            ],
            'deadline' => now()->addDays(fake()->numberBetween(10, 45))->toDateString(),
            'status' => fake()->randomElement(['active', 'active', 'active', 'draft', 'closed', 'archived']),
            'is_active' => true,
            'applications_count' => 0,
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (Job $job): void {
            $job->is_active = $job->status === 'active';
        })->afterCreating(function (Job $job): void {
            if ($job->skills()->exists()) {
                return;
            }

            $job->skills()->sync(
                Skill::query()
                    ->inRandomOrder()
                    ->limit(fake()->numberBetween(3, 6))
                    ->pluck('id')
            );
        });
    }

    public function active(): static
    {
        return $this->state(fn (): array => [
            'status' => 'active',
            'is_active' => true,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (): array => [
            'status' => 'draft',
            'is_active' => false,
        ]);
    }
}
