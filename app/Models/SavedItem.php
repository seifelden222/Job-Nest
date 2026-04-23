<?php

namespace App\Models;

use App\Enums\SavedItemType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SavedItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'target_id',
    ];

    protected function casts(): array
    {
        return [
            'type' => SavedItemType::class,
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
