<?php

namespace Database\Factories;

use App\Models\Document;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    public function definition(): array
    {
        $type = fake()->randomElement(['cv', 'certificate']);
        $title = $type === 'cv'
            ? fake()->randomElement([
                'Professional Resume',
                'Updated Career CV',
                'Backend Engineer Resume',
                'Product Designer CV',
            ])
            : fake()->randomElement([
                'Google Data Analytics Certificate',
                'Meta Front-End Developer Certificate',
                'Digital Marketing Professional Certificate',
                'AWS Cloud Practitioner Badge',
            ]);

        return [
            'user_id' => User::factory()->person(),
            'type' => $type,
            'title' => $title,
            'file_path' => 'documents/'.fake()->uuid().'.pdf',
            'file_name' => str($title)->slug()->append('.pdf')->toString(),
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(80_000, 850_000),
            'is_primary' => $type === 'cv',
        ];
    }

    public function cv(): static
    {
        return $this->state(fn (): array => [
            'type' => 'cv',
            'title' => fake()->randomElement(['Professional CV', 'Updated Resume', 'Senior Candidate CV']),
            'is_primary' => true,
        ]);
    }

    public function certificate(): static
    {
        return $this->state(fn (): array => [
            'type' => 'certificate',
            'title' => fake()->randomElement(['Google Certificate', 'Meta Professional Certificate', 'Project Management Certificate']),
            'is_primary' => false,
        ]);
    }
}
