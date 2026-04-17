<?php

namespace Database\Seeders;

use App\Models\Skill;
use Illuminate\Database\Seeder;

class SkillSeeder extends Seeder
{
    public function run(): void
    {
        $skills = [
            'PHP',
            'Laravel',
            'MySQL',
            'JavaScript',
            'TypeScript',
            'React',
            'Vue.js',
            'Node.js',
            'Python',
            'Docker',
            'Git',
            'REST API',
            'UI/UX',
            'Figma',
            'Communication',
            'Teamwork',
            'Problem Solving',
            'Time Management',
            'Leadership',
            'Project Management',
        ];

        foreach ($skills as $name) {
            Skill::firstOrCreate(['name' => $name]);
        }
    }
}
