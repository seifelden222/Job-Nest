<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Application extends Model
{
    use HasFactory;

    protected $fillable = [
        'job_id',
        'user_id',
        'cv_document_id',
        'cover_letter',
        'status',
        'match_percentage',
        'applied_at',
        'reviewed_at',
        'withdrawn_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'applied_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'withdrawn_at' => 'datetime',
        ];
    }

    public function job(): BelongsTo
    {
        return $this->belongsTo(Job::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function cvDocument(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'cv_document_id');
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }
}
