<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'university',
        'major',
        'employment_status',
        'employment_type',
        'current_job_title',
        'company_name',
        'linkedin_url',
        'portfolio_url',
        'preferred_work_location',
        'expected_salary_min',
        'expected_salary_max',
        'about',
        'onboarding_step',
        'is_profile_completed',
    ];

    protected function casts(): array
    {
        return [
            'expected_salary_min' => 'decimal:2',
            'expected_salary_max' => 'decimal:2',
            'is_profile_completed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
