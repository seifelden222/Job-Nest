<?php

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Support\Facades\Http;

test('user can create and reuse a chatbot conversation', function () {
    $user = createPersonUser();

    config()->set('chatbot.base_url', 'https://chatbot.example.test');

    $this->withToken($user->createToken('chatbot-conversation-store')->plainTextToken)
        ->postJson(route('chatbot.conversations.store'))
        ->assertCreated()
        ->assertJsonPath('data.type', Conversation::TYPE_CHATBOT);

    $conversationId = Conversation::query()
        ->chatbot()
        ->where('created_by', $user->id)
        ->value('id');

    $this->withToken($user->createToken('chatbot-conversation-reuse')->plainTextToken)
        ->postJson(route('chatbot.conversations.store'))
        ->assertOk()
        ->assertJsonPath('data.id', $conversationId);

    expect(Conversation::query()->chatbot()->where('created_by', $user->id)->count())->toBe(1);
});

test('user can send a chatbot message and store the assistant reply', function () {
    Http::fake([
        'chatbot.example.test/*' => Http::response([
            'provider' => 'mock-ai',
            'model' => 'mock-model',
            'reply' => 'JobNest can help you find the right role.',
            'usage' => [
                'prompt_tokens' => 12,
                'completion_tokens' => 18,
                'total_tokens' => 30,
            ],
        ], 200),
    ]);

    config()->set('chatbot.base_url', 'https://chatbot.example.test');

    $user = createPersonUser();
    $conversation = Conversation::factory()->chatbot($user)->create([
        'created_by' => $user->id,
    ]);

    $conversation->participants()->attach([
        $user->id => ['joined_at' => now()],
    ]);

    $this->withToken($user->createToken('chatbot-message-store')->plainTextToken)
        ->postJson(route('chatbot.conversations.messages.store', ['conversation' => $conversation->id]), [
            'body' => 'Help me find a backend job.',
            'source_language' => 'en',
        ])
        ->assertCreated()
        ->assertJsonPath('data.reply.content', 'JobNest can help you find the right role.')
        ->assertJsonPath('data.user_message.message_role', Message::ROLE_USER)
        ->assertJsonPath('data.assistant_message.message_role', Message::ROLE_ASSISTANT);

    expect($conversation->fresh()->last_message_id)->not->toBeNull();

    $messages = Message::query()
        ->where('conversation_id', $conversation->id)
        ->orderBy('id')
        ->get();

    expect($messages)->toHaveCount(2)
        ->and($messages->first()->message_role)->toBe(Message::ROLE_USER)
        ->and($messages->last()->message_role)->toBe(Message::ROLE_ASSISTANT);

    Http::assertSentCount(1);
});

test('only the owner can access chatbot conversations', function () {
    $owner = createPersonUser();
    $outsider = createPersonUser();

    $conversation = Conversation::factory()->chatbot($owner)->create([
        'created_by' => $owner->id,
    ]);

    $conversation->participants()->attach([
        $owner->id => ['joined_at' => now()],
    ]);

    $token = $outsider->createToken('chatbot-forbidden')->plainTextToken;

    $this->withToken($token)
        ->getJson(route('chatbot.conversations.show', ['conversation' => $conversation->id]))
        ->assertForbidden();

    $this->withToken($token)
        ->getJson(route('chatbot.conversations.messages.index', ['conversation' => $conversation->id]))
        ->assertForbidden();

    $this->withToken($token)
        ->postJson(route('chatbot.conversations.messages.store', ['conversation' => $conversation->id]), [
            'body' => 'This should be blocked.',
        ])
        ->assertForbidden();
});
