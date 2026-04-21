<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Software Development', 'type' => 'job'],
            ['name' => 'Marketing', 'type' => 'job'],
            ['name' => 'Design', 'type' => 'job'],
            ['name' => 'Programming', 'type' => 'course'],
            ['name' => 'Data Analysis', 'type' => 'course'],
            ['name' => 'Project Management', 'type' => 'course'],
            ['name' => 'Web Development', 'type' => 'service'],
            ['name' => 'Graphic Design', 'type' => 'service'],
            ['name' => 'Content Writing', 'type' => 'service'],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                [
                    'slug' => Str::slug($category['name']),
                    'type' => $category['type'],
                ],
                [
                    'name' => $category['name'],
                    'description' => $category['name'].' category',
                    'is_active' => true,
                ],
            );
        }
    }
}
