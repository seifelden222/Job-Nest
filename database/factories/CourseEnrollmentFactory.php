<?php

namespace Database\Factories;

use App\Models\Course;
use App\Models\CourseEnrollment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CourseEnrollment>
 */
class CourseEnrollmentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'course_id' => Course::factory(),
            'user_id' => User::factory(),
            'status' => 'enrolled',
            'payment_status' => 'paid',
            'payment_method' => 'card',
            'amount_paid' => 499,
            'enrolled_at' => now(),
            'completed_at' => null,
        ];
    }
}
