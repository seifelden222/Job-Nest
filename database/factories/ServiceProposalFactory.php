<?php

namespace Database\Factories;

use App\Models\ServiceProposal;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ServiceProposal>
 */
class ServiceProposalFactory extends Factory
{
    public function definition(): array
    {
        return [
            'service_request_id' => ServiceRequest::factory(),
            'user_id' => User::factory(),
            'message' => fake()->paragraph(),
            'proposed_budget' => 2000,
            'delivery_days' => fake()->numberBetween(3, 21),
            'status' => 'submitted',
        ];
    }
}
