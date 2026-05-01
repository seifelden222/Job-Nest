<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServiceRequest extends Model
{
    use HasFactory;
    use HasTranslatableAttributes;

    protected array $translatable = [
        'title',
        'description',
    ];

    protected $fillable = [
        'user_id',
        'category_id',
        'title',
        'description',
        'budget_min',
        'budget_max',
        'currency',
        'location',
        'delivery_mode',
        'deadline',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'budget_min' => 'decimal:2',
            'budget_max' => 'decimal:2',
            'deadline' => 'date',
            'title' => 'json:unicode',
            'description' => 'json:unicode',
        ];
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'open');
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
        return $this->belongsToMany(Skill::class, 'service_request_skills')->withTimestamps();
    }

    public function proposals(): HasMany
    {
        return $this->hasMany(ServiceProposal::class);
    }

    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class);
    }
}
