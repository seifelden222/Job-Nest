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
            ['en' => 'Turkish', 'ar' => 'التركية'],
            ['en' => 'Spanish', 'ar' => 'الإسبانية'],
        ];

        foreach ($languages as $language) {
            Language::query()->updateOrCreate(
                ['name->en' => $language['en']],
                ['name' => $language],
            );
        }
    }
}
