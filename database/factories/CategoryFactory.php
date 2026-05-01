<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return [
            'name' => ['en' => Str::title($name), 'ar' => Str::title($name)],
            'slug' => Str::slug($name),
            'type' => fake()->randomElement(['job', 'course', 'service']),
            'description' => ['en' => fake()->sentence(), 'ar' => fake()->sentence()],
            'icon' => null,
            'is_active' => true,
        ];
    }
}
