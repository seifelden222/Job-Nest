<?php

namespace App\Http\Resources\Ai;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ChatbotReplyResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'content' => $this['content'] ?? null,
            'intent' => $this['intent'] ?? null,
            'type' => $this['type'] ?? null,
            'specialty' => $this['specialty'] ?? null,
            'count' => $this['count'] ?? null,
            'results' => $this['results'] ?? [],
            'follow_up' => $this['follow_up'] ?? null,
            'confidence' => $this['confidence'] ?? null,
            'confidence_label' => $this['confidence_label'] ?? null,
            'response_time_ms' => $this['response_time_ms'] ?? null,
        ];
    }
}
