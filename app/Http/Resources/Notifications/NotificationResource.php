<?php

namespace App\Http\Resources\Notifications;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'notification_class' => $this->type,
            'type' => $this->data['type'] ?? 'generic',
            'title' => $this->data['title'] ?? null,
            'body' => $this->data['body'] ?? null,
            'action_type' => $this->data['action_type'] ?? null,
            'related_id' => $this->data['related_id'] ?? null,
            'related_type' => $this->data['related_type'] ?? null,
            'meta' => $this->data['meta'] ?? [],
            'is_read' => $this->read_at !== null,
            'read_at' => $this->read_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
