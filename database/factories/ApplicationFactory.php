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
        return [
            'job_id' => Job::factory(),
            'user_id' => User::factory()->person(),
            'cv_document_id' => Document::factory()->cv(),
            'cover_letter' => ['en' => fake()->paragraph(), 'ar' => fake()->paragraph()],
            'status' => 'submitted',
            'match_percentage' => fake()->numberBetween(50, 95),
            'applied_at' => now(),
            'reviewed_at' => null,
            'withdrawn_at' => null,
            'notes' => null,
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
