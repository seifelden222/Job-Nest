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
        'current_job_title',
        'employment_status',
        'expected_salary_min',
        'expected_salary_max',
        'preferred_work_location',
        'linkedin_url',
        'portfolio_url',
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
