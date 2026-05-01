<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Interest extends Model
{
    use HasFactory;
    use HasTranslatableAttributes;

    protected array $translatable = [
        'name',
    ];

    protected $fillable = [
        'name',
    ];

    protected function casts(): array
    {
        return [
            'name' => 'json:unicode',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_interests')->withTimestamps();
    }
}
