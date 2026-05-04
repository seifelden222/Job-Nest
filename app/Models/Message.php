<?php

namespace App\Models;

use App\Models\Concerns\HasTranslatableAttributes;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;
    use HasTranslatableAttributes;

    public const ROLE_USER = 'user';

    public const ROLE_ASSISTANT = 'assistant';

    public const ROLE_SYSTEM = 'system';

    protected array $translatable = [
        'body',
    ];

    protected $fillable = [
        'conversation_id',
        'sender_id',
        'message_role',
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
            'body' => 'json:unicode',
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

    public function isAssistant(): bool
    {
        return $this->message_role === self::ROLE_ASSISTANT;
    }
}
