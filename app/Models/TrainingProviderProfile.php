<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainingProviderProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'provider_name',
        'website',
        'industry',
        'location',
        'about',
        'logo',
        'is_verified',
        'onboarding_step',
        'is_profile_completed',
    ];

    protected function casts(): array
    {
        return [
            'is_verified' => 'boolean',
            'is_profile_completed' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'training_provider_id');
    }
}
