<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        $languages = [
            'Arabic',
            'English',
            'French',
            'German',
            'Spanish',
            'Italian',
            'Portuguese',
            'Chinese',
            'Japanese',
            'Turkish',
        ];

        foreach ($languages as $name) {
            Language::firstOrCreate(['name' => $name]);
        }
    }
}
