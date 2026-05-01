<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use HasFactory;
    use HasTranslatableAttributes;

    protected array $translatable = [
        'title',
        'short_description',
        'description',
        'course_overview',
        'what_you_learn',
    ];

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'slug',
        'thumbnail',
        'short_description',
        'description',
        'course_overview',
        'what_you_learn',
        'level',
        'delivery_mode',
        'language',
        'price',
        'currency',
        'duration_hours',
        'seats_count',
        'start_date',
        'end_date',
        'status',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_active' => 'boolean',
            'title' => 'json:unicode',
            'short_description' => 'json:unicode',
            'description' => 'json:unicode',
            'course_overview' => 'json:unicode',
            'what_you_learn' => 'json:unicode',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')->where('is_active', true);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function skills(): BelongsToMany
    {
        return $this->belongsToMany(Skill::class, 'course_skills')->withTimestamps();
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(CourseReview::class);
    }
}
