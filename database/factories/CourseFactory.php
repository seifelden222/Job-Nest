<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Course;
use App\Models\Skill;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        $course = fake()->randomElement([
            [
                'title_en' => 'Laravel API Development Bootcamp',
                'title_ar' => 'معسكر تطوير واجهات Laravel البرمجية',
                'summary_en' => 'Build production-ready REST APIs with Laravel, Sanctum, validation, and testing.',
                'summary_ar' => 'تعلّم بناء واجهات برمجية عملية باستخدام Laravel وSanctum والاختبارات.',
            ],
            [
                'title_en' => 'Flutter Career Launch Program',
                'title_ar' => 'برنامج الانطلاق المهني في Flutter',
                'summary_en' => 'Create real mobile apps and prepare a portfolio for junior Flutter roles.',
                'summary_ar' => 'أنشئ تطبيقات حقيقية وابنِ ملف أعمال يؤهلك لوظائف Flutter.',
            ],
            [
                'title_en' => 'Practical Data Analysis with Power BI',
                'title_ar' => 'تحليل البيانات العملي باستخدام Power BI',
                'summary_en' => 'Turn raw business data into dashboards, insights, and reports decision makers can use.',
                'summary_ar' => 'حوّل البيانات الخام إلى لوحات معلومات وتقارير داعمة للقرار.',
            ],
            [
                'title_en' => 'Digital Marketing for Growth Teams',
                'title_ar' => 'التسويق الرقمي لفرق النمو',
                'summary_en' => 'Learn paid acquisition, analytics, content planning, and campaign optimization.',
                'summary_ar' => 'تعلم الإعلانات المدفوعة والتحليلات وتخطيط المحتوى وتحسين الحملات.',
            ],
            [
                'title_en' => 'UI Design Systems in Figma',
                'title_ar' => 'أنظمة التصميم في Figma',
                'summary_en' => 'Design scalable interfaces, component libraries, and polished product handoff workflows.',
                'summary_ar' => 'صمم واجهات قابلة للتوسع ومكتبات مكونات وتسليمات احترافية للمنتج.',
            ],
        ]);

        $titleSlug = Str::slug($course['title_en']).'-'.fake()->unique()->numberBetween(100, 9999);
        $status = fake()->randomElement(['published', 'published', 'published', 'draft', 'closed', 'archived']);

        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory()->state(['type' => 'course']),
            'title' => [
                'en' => $course['title_en'],
                'ar' => $course['title_ar'],
            ],
            'slug' => $titleSlug,
            'url' => 'https://academy.jobnest.test/courses/'.$titleSlug,
            'thumbnail' => 'courses/'.fake()->uuid().'.jpg',
            'short_description' => [
                'en' => $course['summary_en'],
                'ar' => $course['summary_ar'],
            ],
            'description' => [
                'en' => fake()->randomElement([
                    'A practical program built around real deliverables, guided exercises, and market-relevant workflows that learners can use immediately.',
                    'Structured training designed for ambitious learners who want to build confidence through projects, feedback, and applied portfolio work.',
                    'Industry-focused curriculum that combines live examples, practical assignments, and mentor support to accelerate job-ready skills.',
                ]),
                'ar' => 'برنامج تدريبي عملي مصمم لتطوير المهارات المطلوبة في سوق العمل وبناء مشاريع قابلة للعرض.',
            ],
            'course_overview' => [
                'en' => fake()->randomElement([
                    'Includes structured modules, guided practice, downloadable resources, and feedback checkpoints across the full learning path.',
                    'Covers foundations, real-world execution patterns, and a final capstone that learners can showcase in interviews or client work.',
                    'Mixes theory with implementation so participants understand both the principles and the day-to-day workflow of the field.',
                ]),
                'ar' => 'يشمل البرنامج جلسات مباشرة وتمارين تطبيقية ومهام عملية ومتابعة مع المدرب.',
            ],
            'what_you_learn' => [
                'en' => fake()->randomElement([
                    'You will learn the core concepts, tooling, and execution habits needed to contribute confidently on real projects.',
                    'Participants leave with a stronger portfolio, clearer best practices, and a practical understanding of the role’s responsibilities.',
                    'Learners will build repeatable workflows, improve problem solving, and gain language they can use in interviews and client discussions.',
                ]),
                'ar' => 'ستتعلم أساسيات المجال وأفضل الممارسات وكيفية تطبيقها على حالات استخدام حقيقية.',
            ],
            'level' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'delivery_mode' => fake()->randomElement(['online', 'offline', 'hybrid']),
            'language' => fake()->randomElement(['ar', 'en']),
            'price' => fake()->randomElement([0, 499, 999, 1499, 2499, 3999]),
            'currency' => 'EGP',
            'duration_hours' => fake()->numberBetween(6, 48),
            'seats_count' => fake()->numberBetween(15, 120),
            'start_date' => now()->addDays(fake()->numberBetween(5, 40))->toDateString(),
            'end_date' => now()->addDays(fake()->numberBetween(50, 120))->toDateString(),
            'status' => $status,
            'is_active' => $status === 'published',
        ];
    }

    public function published(): static
    {
        return $this->state(fn (): array => [
            'status' => 'published',
            'is_active' => true,
        ]);
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Course $course): void {
            if ($course->skills()->exists()) {
                return;
            }

            $course->skills()->sync(
                Skill::query()
                    ->inRandomOrder()
                    ->limit(fake()->numberBetween(2, 5))
                    ->pluck('id')
            );
        });
    }
}
