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
        $status = fake()->randomElement(['pending', 'enrolled', 'completed', 'cancelled']);
        $amount = fake()->randomElement([0, 499, 999, 1499, 2499, 3999]);
        $paymentStatus = match ($status) {
            'completed', 'enrolled' => fake()->randomElement(['paid', 'paid', 'unpaid']),
            'cancelled' => fake()->randomElement(['failed', 'refunded']),
            default => fake()->randomElement(['unpaid', 'paid']),
        };
        $enrolledAt = now()->subDays(fake()->numberBetween(1, 60));

        return [
            'course_id' => Course::factory(),
            'user_id' => User::factory()->person(),
            'status' => $status,
            'payment_status' => $paymentStatus,
            'payment_method' => $amount === 0 ? 'free' : fake()->randomElement(['card', 'cash']),
            'amount_paid' => $paymentStatus === 'paid' ? $amount : 0,
            'enrolled_at' => $enrolledAt,
            'completed_at' => $status === 'completed' ? (clone $enrolledAt)->addDays(fake()->numberBetween(10, 45)) : null,
        ];
    }
}
