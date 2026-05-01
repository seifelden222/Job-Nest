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
}
