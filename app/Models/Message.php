<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message_type',
        'body',
        'attachment_path',
        'attachment_name',
        'attachment_mime_type',
        'attachment_size',
        'is_edited',
        'edited_at',
    ];

    protected function casts(): array
    {
        return [
            'is_edited' => 'boolean',
            'edited_at' => 'datetime',
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}
