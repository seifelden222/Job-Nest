<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            ['en' => 'Arabic', 'ar' => 'العربية'],
            ['en' => 'English', 'ar' => 'الإنجليزية'],
            ['en' => 'French', 'ar' => 'الفرنسية'],
            ['en' => 'German', 'ar' => 'الألمانية'],
            ['en' => 'Spanish', 'ar' => 'الإسبانية'],
            ['en' => 'Italian', 'ar' => 'الإيطالية'],
            ['en' => 'Portuguese', 'ar' => 'البرتغالية'],
            ['en' => 'Chinese', 'ar' => 'الصينية'],
            ['en' => 'Japanese', 'ar' => 'اليابانية'],
            ['en' => 'Turkish', 'ar' => 'التركية'],
        ];

        foreach ($languages as $language) {
            Language::query()->updateOrCreate(
                ['name->en' => $language['en']],
                ['name' => $language],
            );
        }
    }
}
