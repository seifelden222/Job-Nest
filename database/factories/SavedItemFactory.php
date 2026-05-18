<?php

namespace Database\Factories;

use App\Enums\SavedItemType;
use App\Models\Course;
use App\Models\Job;
use App\Models\SavedItem;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SavedItem>
 */
class SavedItemFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement([
            SavedItemType::Job,
            SavedItemType::Course,
            SavedItemType::ServiceRequest,
        ]);

        return [
            'user_id' => User::factory()->person(),
            'type' => $type,
            'target_id' => match ($type) {
                SavedItemType::Job => Job::factory(),
                SavedItemType::Course => Course::factory(),
                SavedItemType::ServiceRequest => ServiceRequest::factory(),
            },
        ];
    }

    public function forJob(): static
    {
        return $this->state(fn (): array => [
            'type' => SavedItemType::Job,
            'target_id' => Job::factory(),
        ]);
    }

    public function forCourse(): static
    {
        return $this->state(fn (): array => [
            'type' => SavedItemType::Course,
            'target_id' => Course::factory(),
        ]);
    }

    public function forServiceRequest(): static
    {
        return $this->state(fn (): array => [
            'type' => SavedItemType::ServiceRequest,
            'target_id' => ServiceRequest::factory(),
        ]);
    }
}
