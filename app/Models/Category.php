<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;
    use HasTranslatableAttributes;

    protected array $translatable = [
        'name',
        'description',
    ];

    protected $fillable = [
        'name',
        'slug',
        'type',
        'description',
        'icon',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'name' => 'json:unicode',
            'description' => 'json:unicode',
        ];
    }

    public function jobs(): HasMany
    {
        return $this->hasMany(Job::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }

    public function serviceRequests(): HasMany
    {
        return $this->hasMany(ServiceRequest::class);
    }
}
