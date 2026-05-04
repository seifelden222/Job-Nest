<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Conversation;
use App\Models\Job;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Conversation>
 */
class ConversationFactory extends Factory
{
    public function definition(): array
    {
        return [
            'type' => 'direct',
            'application_id' => null,
            'job_id' => null,
            'created_by' => User::factory(),
            'last_message_id' => null,
            'last_message_at' => null,
        ];
    }

    public function chatbot(?User $creator = null): static
    {
        return $this->state(function () use ($creator): array {
            return [
                'type' => Conversation::TYPE_CHATBOT,
                'created_by' => $creator?->id ?? User::factory(),
            ];
        });
    }

    public function applicationType(?Application $application = null, ?Job $job = null, ?User $creator = null): static
    {
        return $this->state(function () use ($application, $job, $creator): array {
            return [
                'type' => 'application',
                'application_id' => $application?->id ?? Application::factory(),
                'job_id' => $job?->id,
                'created_by' => $creator?->id,
            ];
        });
    }
}
