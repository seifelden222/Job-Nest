<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    public function definition(): array
    {
        return [
            'conversation_id' => Conversation::factory(),
            'sender_id' => User::factory(),
            'message_role' => Message::ROLE_USER,
            'message_type' => 'text',
            'body' => ['en' => fake()->sentence(), 'ar' => fake()->sentence()],
            'attachment_path' => null,
            'attachment_name' => null,
            'attachment_mime_type' => null,
            'attachment_size' => null,
            'is_edited' => false,
            'edited_at' => null,
        ];
    }

    public function assistant(): static
    {
        return $this->state(fn (): array => [
            'sender_id' => null,
            'message_role' => Message::ROLE_ASSISTANT,
        ]);
    }
}
