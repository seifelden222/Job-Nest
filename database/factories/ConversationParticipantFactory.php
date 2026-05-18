<?php

namespace Database\Factories;

use App\Models\Conversation;
use App\Models\ConversationParticipant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ConversationParticipant>
 */
class ConversationParticipantFactory extends Factory
{
    public function definition(): array
    {
        $joinedAt = now()->subDays(fake()->numberBetween(0, 20));

        return [
            'conversation_id' => Conversation::factory(),
            'user_id' => User::factory(),
            'joined_at' => $joinedAt,
            'last_read_at' => fake()->boolean(70) ? (clone $joinedAt)->addHours(fake()->numberBetween(1, 96)) : null,
            'is_muted' => fake()->boolean(10),
        ];
    }
}
