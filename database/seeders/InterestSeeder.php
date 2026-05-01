<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;

class InterestSeeder extends Seeder
{
    public function run(): void
    {
        $interests = [
            ['en' => 'Web Development', 'ar' => 'تطوير الويب'],
            ['en' => 'Mobile Development', 'ar' => 'تطوير تطبيقات الجوال'],
            ['en' => 'AI & Machine Learning', 'ar' => 'الذكاء الاصطناعي وتعلم الآلة'],
            ['en' => 'Data Science', 'ar' => 'علم البيانات'],
            ['en' => 'Cybersecurity', 'ar' => 'الأمن السيبراني'],
            ['en' => 'Cloud Computing', 'ar' => 'الحوسبة السحابية'],
            ['en' => 'UI/UX', 'ar' => 'واجهة وتجربة المستخدم'],
            ['en' => 'Graphic Design', 'ar' => 'التصميم الجرافيكي'],
            ['en' => 'Product Management', 'ar' => 'إدارة المنتجات'],
            ['en' => 'Digital Marketing', 'ar' => 'التسويق الرقمي'],
            ['en' => 'Human Resources', 'ar' => 'الموارد البشرية'],
            ['en' => 'Finance & Accounting', 'ar' => 'المالية والمحاسبة'],
            ['en' => 'Game Development', 'ar' => 'تطوير الألعاب'],
            ['en' => 'DevOps', 'ar' => 'ديف أوبس'],
            ['en' => 'Blockchain', 'ar' => 'البلوك تشين'],
        ];

        foreach ($interests as $interest) {
            Interest::query()->updateOrCreate(
                ['name->en' => $interest['en']],
                ['name' => $interest],
            );
        }
    }
}
