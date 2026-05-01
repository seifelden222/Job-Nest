<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            ['en' => 'PHP', 'ar' => 'بي إتش بي'],
            ['en' => 'Laravel', 'ar' => 'لارافيل'],
            ['en' => 'MySQL', 'ar' => 'ماي إس كيو إل'],
            ['en' => 'JavaScript', 'ar' => 'جافاسكربت'],
            ['en' => 'TypeScript', 'ar' => 'تايب سكربت'],
            ['en' => 'React', 'ar' => 'رياكت'],
            ['en' => 'Vue.js', 'ar' => 'فيو جي إس'],
            ['en' => 'Node.js', 'ar' => 'نود جي إس'],
            ['en' => 'Python', 'ar' => 'بايثون'],
            ['en' => 'Docker', 'ar' => 'دوكر'],
            ['en' => 'Git', 'ar' => 'جيت'],
            ['en' => 'REST API', 'ar' => 'واجهة REST البرمجية'],
            ['en' => 'UI/UX', 'ar' => 'واجهة وتجربة المستخدم'],
            ['en' => 'Figma', 'ar' => 'فيجما'],
            ['en' => 'Communication', 'ar' => 'التواصل'],
            ['en' => 'Teamwork', 'ar' => 'العمل الجماعي'],
            ['en' => 'Problem Solving', 'ar' => 'حل المشكلات'],
            ['en' => 'Time Management', 'ar' => 'إدارة الوقت'],
            ['en' => 'Leadership', 'ar' => 'القيادة'],
            ['en' => 'Project Management', 'ar' => 'إدارة المشاريع'],
        ];

        foreach ($skills as $skill) {
            Skill::query()->updateOrCreate(
                ['name->en' => $skill['en']],
                ['name' => $skill],
            );
        }
    }
}
