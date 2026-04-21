<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Course;
use App\Models\TrainingProviderProfile;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Course>
 */
class CourseFactory extends Factory
{
    public function definition(): array
    {
        $title = fake()->unique()->sentence(3);

        return [
            'training_provider_id' => TrainingProviderProfile::factory(),
            'category_id' => Category::factory()->state(['type' => 'course']),
            'title' => $title,
            'slug' => Str::slug($title.'-'.fake()->unique()->numberBetween(1, 9999)),
            'thumbnail' => null,
            'short_description' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'course_overview' => fake()->paragraph(),
            'what_you_learn' => fake()->paragraph(),
            'level' => fake()->randomElement(['beginner', 'intermediate', 'advanced']),
            'delivery_mode' => fake()->randomElement(['online', 'offline', 'hybrid']),
            'language' => 'en',
            'price' => fake()->randomElement([0, 499, 999, 1500]),
            'currency' => 'EGP',
            'duration_hours' => fake()->numberBetween(4, 80),
            'seats_count' => fake()->numberBetween(10, 100),
            'start_date' => now()->addWeek()->toDateString(),
            'end_date' => now()->addWeeks(6)->toDateString(),
            'status' => 'published',
            'is_active' => true,
        ];
    }
}
