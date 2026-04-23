<?php

use App\Models\Course;
use App\Models\Job;
use App\Models\SavedItem;
use App\Models\ServiceRequest;

test('user can save a job', function () {
    $user = createPersonUser();
    $job = Job::factory()->create();

    $response = $this->withToken($user->createToken('saved-job')->plainTextToken)
        ->postJson(route('auth.saved-items.store'), [
            'type' => 'job',
            'target_id' => $job->id,
        ]);

    $response->assertCreated()
        ->assertJsonPath('saved_item.type', 'job')
        ->assertJsonPath('saved_item.target_id', $job->id)
        ->assertJsonPath('saved_item.target.title', $job->title);

    $this->assertDatabaseHas('saved_items', [
        'user_id' => $user->id,
        'type' => 'job',
        'target_id' => $job->id,
    ]);
});

test('user can save a course', function () {
    $user = createCompanyUser();
    $course = Course::factory()->create();

    $this->withToken($user->createToken('saved-course')->plainTextToken)
        ->postJson(route('auth.saved-items.store'), [
            'type' => 'course',
            'target_id' => $course->id,
        ])
        ->assertCreated()
        ->assertJsonPath('saved_item.type', 'course')
        ->assertJsonPath('saved_item.target_id', $course->id)
        ->assertJsonPath('saved_item.target.title', $course->title);
});

test('user can save a service request', function () {
    $user = createPersonUser();
    $serviceRequest = ServiceRequest::factory()->create();

    $this->withToken($user->createToken('saved-service')->plainTextToken)
        ->postJson(route('auth.saved-items.store'), [
            'type' => 'service_request',
            'target_id' => $serviceRequest->id,
        ])
        ->assertCreated()
        ->assertJsonPath('saved_item.type', 'service_request')
        ->assertJsonPath('saved_item.target_id', $serviceRequest->id)
        ->assertJsonPath('saved_item.target.title', $serviceRequest->title);
});

test('duplicate save is rejected cleanly', function () {
    $user = createPersonUser();
    $job = Job::factory()->create();

    SavedItem::query()->create([
        'user_id' => $user->id,
        'type' => 'job',
        'target_id' => $job->id,
    ]);

    $this->withToken($user->createToken('saved-duplicate')->plainTextToken)
        ->postJson(route('auth.saved-items.store'), [
            'type' => 'job',
            'target_id' => $job->id,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['target_id']);
});

test('user can list saved items', function () {
    $user = createPersonUser();
    $job = Job::factory()->create();
    $course = Course::factory()->create();
    $serviceRequest = ServiceRequest::factory()->create();

    SavedItem::query()->create(['user_id' => $user->id, 'type' => 'job', 'target_id' => $job->id]);
    SavedItem::query()->create(['user_id' => $user->id, 'type' => 'course', 'target_id' => $course->id]);
    SavedItem::query()->create(['user_id' => $user->id, 'type' => 'service_request', 'target_id' => $serviceRequest->id]);

    $response = $this->withToken($user->createToken('saved-list')->plainTextToken)
        ->getJson(route('auth.saved-items.index'));

    $response->assertSuccessful()
        ->assertJsonCount(3, 'data')
        ->assertJsonCount(1, 'grouped_data.job')
        ->assertJsonCount(1, 'grouped_data.course')
        ->assertJsonCount(1, 'grouped_data.service_request');
});

test('user can filter saved items by type', function () {
    $user = createPersonUser();
    $job = Job::factory()->create();
    $course = Course::factory()->create();

    SavedItem::query()->create(['user_id' => $user->id, 'type' => 'job', 'target_id' => $job->id]);
    SavedItem::query()->create(['user_id' => $user->id, 'type' => 'course', 'target_id' => $course->id]);

    $response = $this->withToken($user->createToken('saved-filter')->plainTextToken)
        ->getJson(route('auth.saved-items.index', ['type' => 'job']));

    $response->assertSuccessful()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.type', 'job')
        ->assertJsonPath('filters.type', 'job');
});

test('user can remove own saved item', function () {
    $user = createPersonUser();
    $job = Job::factory()->create();

    SavedItem::query()->create([
        'user_id' => $user->id,
        'type' => 'job',
        'target_id' => $job->id,
    ]);

    $this->withToken($user->createToken('saved-delete')->plainTextToken)
        ->deleteJson(route('auth.saved-items.destroy', ['type' => 'job', 'targetId' => $job->id]))
        ->assertSuccessful();

    $this->assertDatabaseMissing('saved_items', [
        'user_id' => $user->id,
        'type' => 'job',
        'target_id' => $job->id,
    ]);
});

test('user cannot remove another users saved item', function () {
    $owner = createPersonUser();
    $otherUser = createCompanyUser();
    $job = Job::factory()->create();

    SavedItem::query()->create([
        'user_id' => $owner->id,
        'type' => 'job',
        'target_id' => $job->id,
    ]);

    $this->withToken($otherUser->createToken('saved-delete-forbidden')->plainTextToken)
        ->deleteJson(route('auth.saved-items.destroy', ['type' => 'job', 'targetId' => $job->id]))
        ->assertNotFound();

    $this->assertDatabaseHas('saved_items', [
        'user_id' => $owner->id,
        'type' => 'job',
        'target_id' => $job->id,
    ]);
});

test('invalid type fails validation', function () {
    $user = createPersonUser();
    $job = Job::factory()->create();

    $this->withToken($user->createToken('saved-invalid-type')->plainTextToken)
        ->postJson(route('auth.saved-items.store'), [
            'type' => 'article',
            'target_id' => $job->id,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['type']);
});

test('invalid target id fails validation', function () {
    $user = createPersonUser();

    $this->withToken($user->createToken('saved-invalid-target')->plainTextToken)
        ->postJson(route('auth.saved-items.store'), [
            'type' => 'job',
            'target_id' => 999999,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['target_id']);
});

test('user can check whether an item is saved', function () {
    $user = createPersonUser();
    $course = Course::factory()->create();

    SavedItem::query()->create([
        'user_id' => $user->id,
        'type' => 'course',
        'target_id' => $course->id,
    ]);

    $this->withToken($user->createToken('saved-check')->plainTextToken)
        ->getJson(route('auth.saved-items.check', [
            'type' => 'course',
            'target_id' => $course->id,
        ]))
        ->assertSuccessful()
        ->assertJsonPath('data.is_saved', true)
        ->assertJsonPath('data.type', 'course')
        ->assertJsonPath('data.target_id', $course->id);
});
