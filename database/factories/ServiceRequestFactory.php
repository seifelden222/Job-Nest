<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\ServiceRequest;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceRequest>
 */
class ServiceRequestFactory extends Factory
{
    public function definition(): array
    {
        $request = fake()->randomElement([
            [
                'title_en' => 'Need a corporate website redesign',
                'title_ar' => 'مطلوب إعادة تصميم موقع شركة',
            ],
            [
                'title_en' => 'Looking for a mobile app MVP',
                'title_ar' => 'أبحث عن تنفيذ نسخة أولية لتطبيق جوال',
            ],
            [
                'title_en' => 'SEO and content plan for product launch',
                'title_ar' => 'خطة محتوى وتحسين محركات بحث لإطلاق منتج',
            ],
            [
                'title_en' => 'Need a dashboard UI kit for SaaS product',
                'title_ar' => 'مطلوب UI Kit للوحة تحكم منتج SaaS',
            ],
            [
                'title_en' => 'Hiring a freelance recruiter for tech roles',
                'title_ar' => 'أبحث عن مسؤول توظيف مستقل لوظائف تقنية',
            ],
        ]);
        $budgetMin = fake()->randomElement([5000, 8000, 12000, 15000, 20000]);
        $budgetMax = $budgetMin + fake()->randomElement([6000, 8000, 12000, 15000]);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory()->state(['type' => 'service']),
            'title' => [
                'en' => $request['title_en'],
                'ar' => $request['title_ar'],
            ],
            'description' => [
                'en' => fake()->randomElement([
                    'We are looking for a reliable specialist with proven delivery experience, clear communication, and the ability to execute within a defined scope and timeline.',
                    'The project needs a practical expert who can take ownership, propose a sensible plan, and deliver polished work aligned with business goals.',
                    'We prefer someone who has handled similar assignments before and can balance speed, collaboration, and quality throughout the engagement.',
                ]),
                'ar' => 'نبحث عن مقدم خدمة محترف بخبرة واضحة لتنفيذ المشروع بجودة عالية وضمن نطاق زمني محدد.',
            ],
            'budget_min' => $budgetMin,
            'budget_max' => $budgetMax,
            'currency' => 'EGP',
            'location' => fake()->randomElement([
                'Cairo, Egypt',
                'Alexandria, Egypt',
                'Remote - Egypt',
                'Remote - GCC',
            ]),
            'delivery_mode' => fake()->randomElement(['online', 'offline', 'hybrid']),
            'deadline' => now()->addDays(fake()->numberBetween(7, 50))->toDateString(),
            'status' => fake()->randomElement(['open', 'open', 'in_progress', 'closed', 'cancelled']),
        ];
    }

    public function open(): static
    {
        return $this->state(fn (): array => [
            'status' => 'open',
        ]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (ServiceRequest $serviceRequest): void {
            if ($serviceRequest->skills()->exists()) {
                return;
            }

            $serviceRequest->skills()->sync(
                Skill::query()
                    ->inRandomOrder()
                    ->limit(fake()->numberBetween(2, 5))
                    ->pluck('id')
            );
        });
    }
}
