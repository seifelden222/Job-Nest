<?php

use App\Models\Application;
use App\Models\Category;
use App\Models\Job;
use App\Models\ServiceProposal;
use App\Models\ServiceRequest;

test('non owner company cannot list another company job applications', function () {
    $owner = createCompanyUser();
    $otherCompany = createCompanyUser();
    $person = createPersonUser();
    $job = Job::factory()->create(['company_id' => $owner->id]);

    Application::factory()->create([
        'job_id' => $job->id,
        'user_id' => $person->id,
        'cv_document_id' => null,
    ]);

    $this->withToken($otherCompany->createToken('applications-index-forbidden')->plainTextToken)
        ->getJson(route('jobs.applications.index', ['job' => $job->id]))
        ->assertForbidden();
});

test('non owner cannot list proposals for another users service request', function () {
    $owner = createCompanyUser();
    $otherUser = createPersonUser();
    $serviceRequest = ServiceRequest::factory()->create(['user_id' => $owner->id]);

    $this->withToken($otherUser->createToken('service-proposals-index-forbidden')->plainTextToken)
        ->getJson(route('service-requests.proposals.index', ['serviceRequest' => $serviceRequest->id]))
        ->assertForbidden();
});

test('outsider cannot view or update a service proposal', function () {
    $owner = createCompanyUser();
    $proposer = createPersonUser();
    $outsider = createPersonUser();
    $serviceRequest = ServiceRequest::factory()->create(['user_id' => $owner->id]);

    $proposal = ServiceProposal::factory()->create([
        'service_request_id' => $serviceRequest->id,
        'user_id' => $proposer->id,
    ]);

    $token = $outsider->createToken('service-proposal-outsider')->plainTextToken;

    $this->withToken($token)
        ->getJson(route('service-proposals.show', ['serviceProposal' => $proposal->id]))
        ->assertForbidden();

    $this->withToken($token)
        ->putJson(route('service-proposals.update', ['serviceProposal' => $proposal->id]), [
            'status' => 'accepted',
        ])
        ->assertForbidden();
});

test('outsider cannot open a service proposal conversation', function () {
    $owner = createCompanyUser();
    $proposer = createPersonUser();
    $outsider = createPersonUser();
    $serviceRequest = ServiceRequest::factory()->create(['user_id' => $owner->id]);

    $proposal = ServiceProposal::factory()->create([
        'service_request_id' => $serviceRequest->id,
        'user_id' => $proposer->id,
    ]);

    $this->withToken($outsider->createToken('service-conversation-outsider')->plainTextToken)
        ->postJson(route('service-proposals.conversation.store', ['serviceProposal' => $proposal->id]))
        ->assertForbidden();
});

test('admin user can manage categories', function () {
    $admin = createAdminUser();

    $createResponse = $this->withToken($admin->createToken('categories-admin')->plainTextToken)
        ->postJson(route('auth.categories.store'), [
            'name' => 'Engineering',
            'type' => 'job',
            'is_active' => true,
        ]);

    $categoryId = $createResponse->assertCreated()->json('data.id');

    $this->withToken($admin->createToken('categories-admin-update')->plainTextToken)
        ->putJson(route('auth.categories.update', ['category' => $categoryId]), [
            'name' => 'Engineering Updated',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Engineering Updated');
});

test('non admin user cannot manage categories', function () {
    $nonAdmin = createCompanyUser();
    $category = Category::factory()->create(['type' => 'job']);

    $this->withToken($nonAdmin->createToken('categories-non-admin')->plainTextToken)
        ->postJson(route('auth.categories.store'), [
            'name' => 'Blocked Category',
            'type' => 'job',
        ])
        ->assertForbidden();

    $this->withToken($nonAdmin->createToken('categories-non-admin-update')->plainTextToken)
        ->putJson(route('auth.categories.update', ['category' => $category->id]), [
            'name' => 'Hijacked Category',
        ])
        ->assertForbidden();
});
