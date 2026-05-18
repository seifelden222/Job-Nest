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
            ['en' => 'Flutter', 'ar' => 'فلاتر'],
            ['en' => 'Dart', 'ar' => 'دارت'],
            ['en' => 'API Integration', 'ar' => 'تكامل الواجهات البرمجية'],
            ['en' => 'Power BI', 'ar' => 'باور بي آي'],
            ['en' => 'SQL', 'ar' => 'إس كيو إل'],
            ['en' => 'Data Visualization', 'ar' => 'تصور البيانات'],
            ['en' => 'SEO', 'ar' => 'تحسين محركات البحث'],
            ['en' => 'Content Strategy', 'ar' => 'استراتيجية المحتوى'],
            ['en' => 'Paid Media', 'ar' => 'الإعلانات المدفوعة'],
            ['en' => 'Brand Design', 'ar' => 'تصميم الهوية'],
            ['en' => 'Motion Graphics', 'ar' => 'الرسوم المتحركة'],
            ['en' => 'Recruitment', 'ar' => 'التوظيف'],
            ['en' => 'CRM', 'ar' => 'إدارة علاقات العملاء'],
            ['en' => 'Customer Success', 'ar' => 'نجاح العملاء'],
            ['en' => 'Business Analysis', 'ar' => 'تحليل الأعمال'],
            ['en' => 'Quality Assurance', 'ar' => 'ضمان الجودة'],
            ['en' => 'Manual Testing', 'ar' => 'الاختبار اليدوي'],
            ['en' => 'Automated Testing', 'ar' => 'الاختبار الآلي'],
            ['en' => 'Agile Delivery', 'ar' => 'التسليم الرشيق'],
            ['en' => 'Copywriting', 'ar' => 'الكتابة الإعلانية'],
        ];

        foreach ($skills as $skill) {
            Skill::query()->updateOrCreate(
                ['name->en' => $skill['en']],
                ['name' => $skill],
            );
        }
    }
}
