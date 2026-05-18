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
            ['en' => 'Software Development', 'ar' => 'تطوير البرمجيات', 'type' => 'job', 'icon' => 'heroicon-o-code-bracket'],
            ['en' => 'Marketing', 'ar' => 'التسويق', 'type' => 'job', 'icon' => 'heroicon-o-megaphone'],
            ['en' => 'Design', 'ar' => 'التصميم', 'type' => 'job', 'icon' => 'heroicon-o-swatch'],
            ['en' => 'Data & Analytics', 'ar' => 'البيانات والتحليلات', 'type' => 'job', 'icon' => 'heroicon-o-chart-bar'],
            ['en' => 'Operations', 'ar' => 'العمليات', 'type' => 'job', 'icon' => 'heroicon-o-briefcase'],
            ['en' => 'Programming', 'ar' => 'البرمجة', 'type' => 'course', 'icon' => 'heroicon-o-code-bracket-square'],
            ['en' => 'Data Analysis', 'ar' => 'تحليل البيانات', 'type' => 'course', 'icon' => 'heroicon-o-presentation-chart-line'],
            ['en' => 'Project Management', 'ar' => 'إدارة المشاريع', 'type' => 'course', 'icon' => 'heroicon-o-clipboard-document-check'],
            ['en' => 'Product Design', 'ar' => 'تصميم المنتجات', 'type' => 'course', 'icon' => 'heroicon-o-sparkles'],
            ['en' => 'Digital Marketing', 'ar' => 'التسويق الرقمي', 'type' => 'course', 'icon' => 'heroicon-o-signal'],
            ['en' => 'Web Development', 'ar' => 'تطوير الويب', 'type' => 'service', 'icon' => 'heroicon-o-globe-alt'],
            ['en' => 'Graphic Design', 'ar' => 'التصميم الجرافيكي', 'type' => 'service', 'icon' => 'heroicon-o-paint-brush'],
            ['en' => 'Content Writing', 'ar' => 'كتابة المحتوى', 'type' => 'service', 'icon' => 'heroicon-o-pencil-square'],
            ['en' => 'Recruitment Services', 'ar' => 'خدمات التوظيف', 'type' => 'service', 'icon' => 'heroicon-o-user-group'],
            ['en' => 'Mobile App Development', 'ar' => 'تطوير تطبيقات الجوال', 'type' => 'service', 'icon' => 'heroicon-o-device-phone-mobile'],
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
                        'en' => 'Curated '.$category['en'].' opportunities for the JobNest marketplace.',
                        'ar' => 'فئة مخصصة لفرص '.$category['ar'].' داخل سوق JobNest.',
                    ],
                    'icon' => $category['icon'],
                    'is_active' => true,
                ],
            );
        }
    }
}
