<?php

namespace App\Notifications\Messages;

use App\Models\Message;
use App\Models\User;
use Illuminate\Notifications\Notification;

class NewMessageReceivedNotification extends Notification
{
    public function __construct(
        public Message $message,
        public User $sender,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $bodyPreview = $this->message->message_type === 'file'
            ? 'Sent you an attachment.'
            : (string) ($this->message->body ?? 'Sent you a new message.');

        return [
            'type' => 'new_message_received',
            'title' => 'New Message Received',
            'body' => sprintf('%s: %s', $this->sender->name, $bodyPreview),
            'action_type' => 'new_message',
            'related_id' => $this->message->id,
            'related_type' => 'message',
            'meta' => [
                'conversation_id' => $this->message->conversation_id,
                'sender_id' => $this->sender->id,
                'sender_name' => $this->sender->name,
                'message_type' => $this->message->message_type,
                'preview' => $bodyPreview,
            ],
        ];
    }
}
