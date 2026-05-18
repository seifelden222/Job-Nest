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
            'body' => [
                'en' => fake()->randomElement([
                    'Thanks for the update. I reviewed the details and I am available to move forward on the next step.',
                    'This looks aligned with what I expected. Please let me know the timeline and any materials you need from my side.',
                    'I appreciate the quick response. I can join a call this week to discuss scope, expectations, and delivery milestones.',
                ]),
                'ar' => 'شكرًا على التحديث، سأراجع التفاصيل وأعود إليكم قريبًا.',
            ],
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
            'body' => [
                'en' => fake()->randomElement([
                    'Based on your profile and recent activity, these are the strongest opportunities currently matching your background.',
                    'I found a few courses and jobs that align with your skills, preferred work style, and recent engagement patterns.',
                    'Here are some tailored suggestions that fit your profile, saved interests, and the roles you interacted with most recently.',
                ]),
                'ar' => 'بناءً على ملفك الحالي، هذه أفضل الخيارات التي قد تناسب اهتماماتك وخبراتك.',
            ],
        ]);
    }

    public function system(): static
    {
        return $this->state(fn (): array => [
            'sender_id' => null,
            'message_role' => Message::ROLE_SYSTEM,
            'message_type' => 'system',
            'body' => [
                'en' => 'Conversation created successfully.',
                'ar' => 'تم إنشاء المحادثة بنجاح.',
            ],
        ]);
    }
}
