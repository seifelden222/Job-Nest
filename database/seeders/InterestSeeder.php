<?php

namespace Database\Seeders;

use App\Models\Interest;
use Illuminate\Database\Seeder;

class InterestSeeder extends Seeder
{
    public function run(): void
    {
        $interests = [
            'Web Development',
            'Mobile Development',
            'AI & Machine Learning',
            'Data Science',
            'Cybersecurity',
            'Cloud Computing',
            'UI/UX',
            'Graphic Design',
            'Product Management',
            'Digital Marketing',
            'Human Resources',
            'Finance & Accounting',
            'Game Development',
            'DevOps',
            'Blockchain',
        ];

        foreach ($interests as $name) {
            Interest::firstOrCreate(['name' => $name]);
        }
    }
}
