<?php

use App\Models\Application;
use App\Models\Conversation;
use App\Models\Job;
use App\Models\Message;
use Illuminate\Support\Facades\Storage;

test('user can create direct conversation', function () {
    $user = createPersonUser();
    $otherUser = createCompanyUser();

    $this->withToken($user->createToken('conversation-store-direct')->plainTextToken)
        ->postJson(route('conversations.store'), [
            'type' => 'direct',
            'participant_id' => $otherUser->id,
        ])
        ->assertCreated()
        ->assertJsonPath('data.type', 'direct')
        ->assertJsonCount(2, 'data.participants');
});

test('application participant can create application conversation', function () {
    $company = createCompanyUser();
    $person = createPersonUser();
    $job = Job::factory()->create(['company_id' => $company->id]);
    $application = Application::factory()->create([
        'job_id' => $job->id,
        'user_id' => $person->id,
        'cv_document_id' => null,
    ]);

    $this->withToken($person->createToken('conversation-store-application')->plainTextToken)
        ->postJson(route('conversations.store'), [
            'type' => 'application',
            'application_id' => $application->id,
        ])
        ->assertCreated()
        ->assertJsonPath('data.type', 'application')
        ->assertJsonPath('data.application.id', $application->id);
});

test('user only sees participant conversations', function () {
    $user = createPersonUser();
    $company = createCompanyUser();
    $outsider = createPersonUser();

    $visibleConversation = Conversation::factory()->create([
        'type' => 'direct',
        'created_by' => $user->id,
    ]);
    $visibleConversation->participants()->attach([
        $user->id => ['joined_at' => now()],
        $company->id => ['joined_at' => now()],
    ]);

    $hiddenConversation = Conversation::factory()->create([
        'type' => 'direct',
        'created_by' => $company->id,
    ]);
    $hiddenConversation->participants()->attach([
        $company->id => ['joined_at' => now()],
        $outsider->id => ['joined_at' => now()],
    ]);

    $this->withToken($user->createToken('conversation-index')->plainTextToken)
        ->getJson(route('conversations.index'))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.data')
        ->assertJsonPath('data.data.0.id', $visibleConversation->id);
});

test('only participants can view conversation', function () {
    $user = createPersonUser();
    $otherUser = createCompanyUser();
    $outsider = createPersonUser();
    $conversation = Conversation::factory()->create([
        'type' => 'direct',
        'created_by' => $user->id,
    ]);
    $conversation->participants()->attach([
        $user->id => ['joined_at' => now()],
        $otherUser->id => ['joined_at' => now()],
    ]);

    $this->withToken($outsider->createToken('conversation-show-forbidden')->plainTextToken)
        ->getJson(route('conversations.show', ['conversation' => $conversation->id]))
        ->assertForbidden();
});

test('participant can list messages', function () {
    $user = createPersonUser();
    $otherUser = createCompanyUser();
    $conversation = Conversation::factory()->create(['created_by' => $user->id]);
    $conversation->participants()->attach([
        $user->id => ['joined_at' => now()],
        $otherUser->id => ['joined_at' => now()],
    ]);
    Message::factory()->count(2)->create([
        'conversation_id' => $conversation->id,
        'sender_id' => $user->id,
    ]);

    $this->withToken($user->createToken('messages-index')->plainTextToken)
        ->getJson(route('conversations.messages.index', ['conversation' => $conversation->id]))
        ->assertSuccessful()
        ->assertJsonCount(2, 'data.data');
});

test('participant can send message and last message timestamp updates', function () {
    Storage::fake('public');
    $user = createPersonUser();
    $otherUser = createCompanyUser();
    $conversation = Conversation::factory()->create(['created_by' => $user->id]);
    $conversation->participants()->attach([
        $user->id => ['joined_at' => now()],
        $otherUser->id => ['joined_at' => now()],
    ]);

    $this->withToken($user->createToken('messages-store')->plainTextToken)
        ->postJson(route('conversations.messages.store', ['conversation' => $conversation->id]), [
            'body' => 'Hello from JobNest.',
        ])
        ->assertCreated()
        ->assertJsonPath('data.body', 'Hello from JobNest.');

    expect($conversation->fresh()->last_message_at)->not->toBeNull()
        ->and($conversation->fresh()->last_message_id)->not->toBeNull();
});

test('non participant cannot view or send messages', function () {
    $user = createPersonUser();
    $otherUser = createCompanyUser();
    $outsider = createPersonUser();
    $conversation = Conversation::factory()->create(['created_by' => $user->id]);
    $conversation->participants()->attach([
        $user->id => ['joined_at' => now()],
        $otherUser->id => ['joined_at' => now()],
    ]);

    $token = $outsider->createToken('messages-forbidden')->plainTextToken;

    $this->withToken($token)
        ->getJson(route('conversations.messages.index', ['conversation' => $conversation->id]))
        ->assertForbidden();

    $this->withToken($token)
        ->postJson(route('conversations.messages.store', ['conversation' => $conversation->id]), [
            'body' => 'I should not be here.',
        ])
        ->assertForbidden();
});
