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
            ['en' => 'Software Development', 'ar' => 'تطوير البرمجيات', 'type' => 'job'],
            ['en' => 'Marketing', 'ar' => 'التسويق', 'type' => 'job'],
            ['en' => 'Design', 'ar' => 'التصميم', 'type' => 'job'],
            ['en' => 'Programming', 'ar' => 'البرمجة', 'type' => 'course'],
            ['en' => 'Data Analysis', 'ar' => 'تحليل البيانات', 'type' => 'course'],
            ['en' => 'Project Management', 'ar' => 'إدارة المشاريع', 'type' => 'course'],
            ['en' => 'Web Development', 'ar' => 'تطوير الويب', 'type' => 'service'],
            ['en' => 'Graphic Design', 'ar' => 'التصميم الجرافيكي', 'type' => 'service'],
            ['en' => 'Content Writing', 'ar' => 'كتابة المحتوى', 'type' => 'service'],
        ];

        foreach ($categories as $category) {
            Category::query()->updateOrCreate(
                [
                    'slug' => Str::slug($category['en']),
                    'type' => $category['type'],
                ],
                [
                    'name' => [
                        'en' => $category['en'],
                        'ar' => $category['ar'],
                    ],
                    'description' => [
                        'en' => $category['en'].' category',
                        'ar' => 'فئة '.$category['ar'],
                    ],
                    'is_active' => true,
                ],
            );
        }
    }
}
