<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Document;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    public function definition(): array
    {
        $status = fake()->randomElement([
            'submitted',
            'submitted',
            'under_review',
            'shortlisted',
            'interview_scheduled',
            'offered',
            'accepted',
            'rejected',
            'withdrawn',
        ]);

        return [
            'job_id' => Job::factory(),
            'user_id' => User::factory()->person(),
            'cv_document_id' => fn (array $attributes): int => Document::factory()
                ->cv()
                ->create(['user_id' => $attributes['user_id']])
                ->getKey(),
            'cover_letter' => [
                'en' => fake()->randomElement([
                    'I am excited about this role because it matches the kind of practical product work I enjoy, and I believe my recent hands-on experience would help me contribute quickly.',
                    'This opportunity aligns well with my background and the direction I want to grow in. I would value the chance to bring strong ownership and dependable execution to the team.',
                    'I am applying because the role sits at the intersection of the skills I use most often and the business impact I want to create in my next position.',
                ]),
                'ar' => 'أرغب في الانضمام إلى هذا الدور لأن لدي خبرة مناسبة وشغف واضح بالمجال وقدرة على تقديم قيمة سريعة للفريق.',
            ],
            'status' => $status,
            'match_percentage' => fake()->numberBetween(58, 97),
            'applied_at' => now()->subDays(fake()->numberBetween(1, 25)),
            'reviewed_at' => in_array($status, ['under_review', 'shortlisted', 'interview_scheduled', 'offered', 'accepted', 'rejected'], true)
                ? now()->subDays(fake()->numberBetween(0, 10))
                : null,
            'withdrawn_at' => $status === 'withdrawn'
                ? now()->subDays(fake()->numberBetween(0, 5))
                : null,
            'notes' => in_array($status, ['shortlisted', 'interview_scheduled', 'offered', 'accepted', 'rejected'], true)
                ? fake()->sentence()
                : null,
        ];
    }

    public function reviewed(): static
    {
        return $this->state(fn (): array => [
            'status' => 'under_review',
            'reviewed_at' => now(),
        ]);
    }
}
