<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseReview;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseReview>
 */
class CourseReviewFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'user_id' => User::factory()->person(),
            'rating' => fake()->numberBetween(3, 5),
            'comment' => [
                'en' => fake()->randomElement([
                    'The structure was clear, the examples were practical, and I could apply what I learned immediately in my own projects.',
                    'A very useful course for building confidence step by step, especially because the assignments felt close to real market work.',
                    'Strong content and well-paced delivery. The material helped me organize what I knew and fill the gaps that were holding me back.',
                ]),
                'ar' => 'المحتوى منظم والمدرب واضح والمادة التدريبية مفيدة للغاية.',
            ],
        ];
    }
}
