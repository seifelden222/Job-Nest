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
        $budget = fake()->randomElement([6000, 9000, 12000, 18000, 24000, 32000]);

        return [
            'service_request_id' => ServiceRequest::factory(),
            'user_id' => User::factory(),
            'message' => [
                'en' => fake()->randomElement([
                    'I have delivered similar work for growing teams and can handle the scope with clear milestones, fast communication, and dependable follow-through.',
                    'My proposal focuses on practical execution, transparent updates, and a delivery plan that keeps quality high without slowing down the project.',
                    'I can support this request end to end, from scoping and setup to final delivery and handover, with attention to both quality and business outcomes.',
                ]),
                'ar' => 'أمتلك خبرة عملية في تنفيذ مشاريع مشابهة ويمكنني تسليم العمل بجودة عالية خلال المدة المقترحة.',
            ],
            'proposed_budget' => $budget,
            'delivery_days' => fake()->numberBetween(5, 30),
            'status' => fake()->randomElement(['submitted', 'submitted', 'accepted', 'rejected', 'withdrawn']),
        ];
    }
}
