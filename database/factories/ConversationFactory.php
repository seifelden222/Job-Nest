<?php

namespace Database\Factories;

use App\Models\Application;
use App\Models\Conversation;
use App\Models\Job;
use App\Models\ServiceProposal;
use App\Models\ServiceRequest;
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
            'type' => fake()->randomElement(['direct', 'application', 'service', Conversation::TYPE_CHATBOT]),
            'application_id' => null,
            'job_id' => null,
            'service_request_id' => null,
            'service_proposal_id' => null,
            'created_by' => User::factory(),
            'last_message_id' => null,
            'last_message_at' => null,
        ];
    }

    public function chatbot(?User $creator = null): static
    {
        return $this->state(fn (): array => [
            'type' => Conversation::TYPE_CHATBOT,
            'created_by' => $creator?->id ?? User::factory()->person(),
        ]);
    }

    public function applicationType(?Application $application = null, ?Job $job = null, ?User $creator = null): static
    {
        return $this->state(fn (): array => [
            'type' => 'application',
            'application_id' => $application?->id ?? Application::factory(),
            'job_id' => $job?->id ?? $application?->job_id,
            'created_by' => $creator?->id ?? User::factory(),
        ]);
    }

    public function serviceType(?ServiceRequest $serviceRequest = null, ?ServiceProposal $serviceProposal = null, ?User $creator = null): static
    {
        return $this->state(fn (): array => [
            'type' => 'service',
            'service_request_id' => $serviceRequest?->id ?? ServiceRequest::factory(),
            'service_proposal_id' => $serviceProposal?->id,
            'created_by' => $creator?->id ?? User::factory(),
        ]);
    }
}
