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
        $category = fake()->randomElement([
            ['en' => 'Software Development', 'ar' => 'تطوير البرمجيات', 'type' => 'job', 'icon' => 'heroicon-o-code-bracket'],
            ['en' => 'Product Design', 'ar' => 'تصميم المنتجات', 'type' => 'job', 'icon' => 'heroicon-o-swatch'],
            ['en' => 'Digital Marketing', 'ar' => 'التسويق الرقمي', 'type' => 'service', 'icon' => 'heroicon-o-megaphone'],
            ['en' => 'Data & Analytics', 'ar' => 'البيانات والتحليلات', 'type' => 'course', 'icon' => 'heroicon-o-chart-bar'],
        ]);

        return [
            'name' => ['en' => $category['en'], 'ar' => $category['ar']],
            'slug' => Str::slug($category['en']),
            'type' => $category['type'],
            'description' => [
                'en' => 'Curated category for '.$category['en'].' opportunities on JobNest.',
                'ar' => 'فئة مخصصة لفرص '.$category['ar'].' على منصة JobNest.',
            ],
            'icon' => $category['icon'],
            'is_active' => true,
        ];
    }
}
