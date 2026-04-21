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
        return [
            'user_id' => User::factory(),
            'type' => 'cv',
            'title' => fake()->sentence(3),
            'file_path' => 'documents/'.fake()->uuid().'.pdf',
            'file_name' => fake()->slug().'.pdf',
            'mime_type' => 'application/pdf',
            'file_size' => fake()->numberBetween(50_000, 500_000),
            'is_primary' => true,
        ];
    }

    public function cv(): static
    {
        return $this->state(fn (): array => [
            'type' => 'cv',
            'is_primary' => true,
        ]);
    }

    public function certificate(): static
    {
        return $this->state(fn (): array => [
            'type' => 'certificate',
            'is_primary' => false,
        ]);
    }
}
