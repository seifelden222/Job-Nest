<?php

namespace App\Services\Ai;

use App\Models\Job;
use Illuminate\Support\Str;

class JobAiPayloadBuilder
{
    /**
     * @return array<string, mixed>
     */
    public function build(Job $job): array
    {
        $job->loadMissing([
            'company:id,name',
            'company.companyProfile',
            'skills:id,name',
        ]);

        return [
            'company_name' => (string) ($job->company?->companyProfile?->company_name ?? $job->company?->name ?? ''),
            'title' => $this->stringValue($job->title),
            'job_required_skills' => $job->skills->pluck('name')->filter()->implode('|'),
            'job_location' => (string) ($job->location ?: 'Remote'),
            'industry' => (string) ($job->industry ?: 'Technology'),
            'job_type' => $this->jobType((string) $job->employment_type),
            'salary_range_egp' => $this->salaryRange($job->salary_min, $job->salary_max),
            'experience_required' => $this->experienceRequired((string) $job->experience_level),
            'description' => $this->stringValue($job->description),
        ];
    }

    /**
     * @return list<string>
     */
    public function requiredFields(): array
    {
        return ['company_name', 'title', 'job_required_skills'];
    }

    private function stringValue(mixed $value): string
    {
        return is_string($value) ? trim($value) : '';
    }

    private function jobType(string $employmentType): string
    {
        return $employmentType !== ''
            ? Str::title(str_replace('_', ' ', $employmentType))
            : 'Full Time';
    }

    private function salaryRange(mixed $min, mixed $max): string
    {
        $salaryMin = is_numeric($min) ? (int) round((float) $min) : 0;
        $salaryMax = is_numeric($max) ? (int) round((float) $max) : 0;

        return $salaryMin.'-'.$salaryMax;
    }

    private function experienceRequired(string $experienceLevel): string
    {
        $normalized = mb_strtolower(trim($experienceLevel));

        if (preg_match('/\d+/', $normalized, $matches) === 1) {
            return $matches[0];
        }

        return match ($normalized) {
            'entry', 'entry level', 'internship', 'intern', 'beginner', 'junior' => '0',
            'mid', 'middle', 'mid level' => '3',
            'senior', 'lead', 'principal' => '5',
            default => '0',
        };
    }
}
