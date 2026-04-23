<?php

use App\Models\Application;
use App\Models\Conversation;
use App\Models\Job;
use App\Models\Skill;
use App\Models\User;
use App\Notifications\Applications\ApplicationStatusUpdatedNotification;
use App\Notifications\Jobs\NewJobPostedNotification;

function createNotificationForUser(User $user, string $title): void
{
    $job = Job::factory()->create([
        'title' => $title,
    ]);

    $user->notify(new NewJobPostedNotification($job->load('skills')));
}

test('authenticated user can list own notifications', function () {
    $user = createPersonUser();

    createNotificationForUser($user, 'First Notification');
    createNotificationForUser($user, 'Second Notification');

    $this->withToken($user->createToken('notifications-index')->plainTextToken)
        ->getJson(route('auth.notifications.index'))
        ->assertSuccessful()
        ->assertJsonPath('message', 'Notifications fetched successfully.')
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('unread_count', 2);
});

test('authenticated user can get unread notifications count', function () {
    $user = createPersonUser();

    createNotificationForUser($user, 'Unread A');
    createNotificationForUser($user, 'Unread B');

    $latestNotification = $user->notifications()->latest()->first();
    $latestNotification?->markAsRead();

    $this->withToken($user->createToken('notifications-unread-count')->plainTextToken)
        ->getJson(route('auth.notifications.unread-count'))
        ->assertSuccessful()
        ->assertJsonPath('unread_count', 1);
});

test('authenticated user can mark a single notification as read', function () {
    $user = createPersonUser();

    createNotificationForUser($user, 'Mark Me Read');

    $notificationId = (string) $user->notifications()->latest()->value('id');

    $this->withToken($user->createToken('notifications-mark-one')->plainTextToken)
        ->patchJson(route('auth.notifications.mark-read', ['notification' => $notificationId]))
        ->assertSuccessful()
        ->assertJsonPath('message', 'Notification marked as read.')
        ->assertJsonPath('data.id', $notificationId)
        ->assertJsonPath('data.is_read', true)
        ->assertJsonPath('unread_count', 0);

    expect($user->fresh()->notifications()->find($notificationId)?->read_at)->not->toBeNull();
});

test('authenticated user can mark all notifications as read', function () {
    $user = createPersonUser();

    createNotificationForUser($user, 'Mark All A');
    createNotificationForUser($user, 'Mark All B');

    $this->withToken($user->createToken('notifications-mark-all')->plainTextToken)
        ->patchJson(route('auth.notifications.mark-all-read'))
        ->assertSuccessful()
        ->assertJsonPath('message', 'All notifications marked as read.')
        ->assertJsonPath('data.marked_count', 2)
        ->assertJsonPath('unread_count', 0);

    expect($user->fresh()->unreadNotifications()->count())->toBe(0);
});

test('authenticated user can delete own notification only', function () {
    $user = createPersonUser();
    $otherUser = createPersonUser();

    createNotificationForUser($user, 'Delete Me');
    createNotificationForUser($otherUser, 'Keep Me');

    $notificationId = (string) $user->notifications()->latest()->value('id');
    $otherNotificationId = (string) $otherUser->notifications()->latest()->value('id');

    $this->withToken($user->createToken('notifications-delete')->plainTextToken)
        ->deleteJson(route('auth.notifications.destroy', ['notification' => $notificationId]))
        ->assertSuccessful()
        ->assertJsonPath('message', 'Notification deleted successfully.');

    $this->assertDatabaseMissing('notifications', ['id' => $notificationId]);
    $this->assertDatabaseHas('notifications', ['id' => $otherNotificationId]);

    $this->withToken($user->createToken('notifications-delete-forbidden')->plainTextToken)
        ->deleteJson(route('auth.notifications.destroy', ['notification' => $otherNotificationId]))
        ->assertNotFound();
});

test('application status update creates notification for applicant', function () {
    $company = createCompanyUser();
    $person = createPersonUser();
    $job = Job::factory()->create(['company_id' => $company->id]);
    $application = Application::factory()->create([
        'job_id' => $job->id,
        'user_id' => $person->id,
        'status' => 'submitted',
        'cv_document_id' => null,
    ]);

    $this->withToken($company->createToken('application-status-notify')->plainTextToken)
        ->putJson(route('applications.update', ['application' => $application->id]), [
            'status' => 'under_review',
            'notes' => 'Initial review started.',
        ])
        ->assertSuccessful();

    $notification = $person->fresh()->notifications()->latest()->first();

    expect($notification)->not->toBeNull()
        ->and($notification?->type)->toBe(ApplicationStatusUpdatedNotification::class)
        ->and($notification?->data['action_type'])->toBe('application_status_updated');
});

test('sending a new message creates notifications for other participants except sender', function () {
    $sender = createPersonUser();
    $recipient = createCompanyUser();

    $conversation = Conversation::factory()->create([
        'type' => 'direct',
        'created_by' => $sender->id,
    ]);

    $conversation->participants()->attach([
        $sender->id => ['joined_at' => now()],
        $recipient->id => ['joined_at' => now()],
    ]);

    $this->withToken($sender->createToken('message-notify')->plainTextToken)
        ->postJson(route('conversations.messages.store', ['conversation' => $conversation->id]), [
            'body' => 'Hello recipient.',
            'message_type' => 'text',
        ])
        ->assertCreated();

    $recipientNotification = $recipient->fresh()->notifications()->latest()->first();

    expect($recipientNotification)->not->toBeNull()
        ->and($recipientNotification?->data['action_type'])->toBe('new_message');

    expect($sender->fresh()->notifications()->count())->toBe(0);
});

test('creating an active job notifies matching users by skills', function () {
    $company = createCompanyUser();
    $person = createPersonUser();
    $skill = Skill::create(['name' => 'Laravel']);

    $person->skills()->attach($skill->id);

    $this->withToken($company->createToken('job-notify')->plainTextToken)
        ->postJson(route('jobs.store'), [
            'title' => 'Laravel Backend Engineer',
            'description' => 'Build APIs with Laravel.',
            'location' => 'Cairo',
            'employment_type' => 'full_time',
            'salary_min' => 8000,
            'salary_max' => 12000,
            'currency' => 'EGP',
            'experience_level' => 'mid',
            'requirements' => 'Laravel and SQL.',
            'responsibilities' => 'Build API modules.',
            'deadline' => now()->addWeek()->toDateString(),
            'status' => 'active',
            'skill_ids' => [$skill->id],
        ])
        ->assertCreated();

    $notification = $person->fresh()->notifications()->latest()->first();

    expect($notification)->not->toBeNull()
        ->and($notification?->data['action_type'])->toBe('job_posted')
        ->and($notification?->data['related_type'])->toBe('job');
});
