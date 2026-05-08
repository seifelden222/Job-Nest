<?php

namespace App\Services\Ai;

use App\Models\Course;
use Illuminate\Support\Str;

class CourseAiPayloadBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(Course $course): array
    {
        $course->loadMissing([
            'category:id,name',
            'skills:id,name',
            'owner:id,name',
        ]);

        return [
            'title' => $this->stringValue($course->title),
            'specialty' => (string) ($course->category?->name ?? ''),
            'platform' => 'JobNest',
            'level' => Str::title((string) $course->level),
            'language' => $this->mapLanguage((string) $course->language),
            'price' => (float) $course->price <= 0 ? 'Free' : 'Paid',
            'rating' => 0,
            'skills' => $course->skills->pluck('name')->filter()->implode('|'),
            'instructor' => (string) ($course->owner?->name ?? ''),
            'duration' => $course->duration_hours ? $course->duration_hours.' hours' : '',
            'certificate' => '',
            'url' => (string) ($course->url ?? ''),
        ];
    }

    /**
     * @return list<string>
     */
    public function requiredFields(): array
    {
        return ['title', 'specialty'];
    }

    private function stringValue(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    private function mapLanguage(string $language): string
    {
        return match (mb_strtolower($language)) {
            'ar' => 'Arabic',
            'en' => 'English',
            default => $language !== '' ? Str::title(str_replace('_', ' ', $language)) : 'English',
        };
    }
}
