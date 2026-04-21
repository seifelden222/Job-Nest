<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ServiceProposal extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_request_id',
        'user_id',
        'message',
        'proposed_budget',
        'delivery_days',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'proposed_budget' => 'decimal:2',
        ];
    }

    public function serviceRequest(): BelongsTo
    {
        return $this->belongsTo(ServiceRequest::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function conversation(): HasOne
    {
        return $this->hasOne(Conversation::class);
    }
}
