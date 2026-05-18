<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PortfolioItem extends Model
{
    use HasFactory;
    use HasTranslatableAttributes;

    protected array $translatable = ['title', 'description'];

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'project_url',
        'github_url',
        'thumbnail',
        'technologies',
        'role',
        'start_date',
        'end_date',
        'is_featured',
        'is_public',
    ];

    protected function casts(): array
    {
        return [
            'title' => 'json:unicode',
            'description' => 'json:unicode',
            'technologies' => 'json',
            'start_date' => 'date',
            'end_date' => 'date',
            'is_featured' => 'boolean',
            'is_public' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
