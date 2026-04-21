<?php

use App\Models\Application;
use App\Models\Document;
use App\Models\Job;

test('person can apply to active job', function () {
    $company = createCompanyUser();
    $person = createPersonUser();
    $job = Job::factory()->create(['company_id' => $company->id]);
    $cv = Document::factory()->cv()->create(['user_id' => $person->id]);

    $this->withToken($person->createToken('application-store')->plainTextToken)
        ->postJson(route('jobs.applications.store', ['job' => $job->id]), [
            'cv_document_id' => $cv->id,
            'cover_letter' => 'I am a strong fit for this role.',
        ])
        ->assertCreated()
        ->assertJsonPath('data.user_id', $person->id)
        ->assertJsonPath('data.cv_document_id', $cv->id)
        ->assertJsonPath('data.cover_letter', 'I am a strong fit for this role.');

    expect($job->fresh()->applications_count)->toBe(1);
});

test('person cannot apply twice to same job', function () {
    $company = createCompanyUser();
    $person = createPersonUser();
    $job = Job::factory()->create(['company_id' => $company->id]);
    Application::factory()->create([
        'job_id' => $job->id,
        'user_id' => $person->id,
        'cv_document_id' => null,
    ]);

    $this->withToken($person->createToken('application-duplicate')->plainTextToken)
        ->postJson(route('jobs.applications.store', ['job' => $job->id]), [])
        ->assertStatus(409)
        ->assertJsonPath('message', 'You already applied to this job.');
});

test('company cannot apply as applicant', function () {
    $company = createCompanyUser();
    $job = Job::factory()->create(['company_id' => $company->id]);

    $this->withToken($company->createToken('company-apply')->plainTextToken)
        ->postJson(route('jobs.applications.store', ['job' => $job->id]), [])
        ->assertForbidden();
});

test('cv document must belong to applicant', function () {
    $company = createCompanyUser();
    $person = createPersonUser();
    $otherPerson = createPersonUser();
    $job = Job::factory()->create(['company_id' => $company->id]);
    $foreignCv = Document::factory()->cv()->create(['user_id' => $otherPerson->id]);

    $this->withToken($person->createToken('application-cv-ownership')->plainTextToken)
        ->postJson(route('jobs.applications.store', ['job' => $job->id]), [
            'cv_document_id' => $foreignCv->id,
        ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['cv_document_id']);
});

test('company can list applications for own jobs', function () {
    $company = createCompanyUser();
    $person = createPersonUser();
    $job = Job::factory()->create(['company_id' => $company->id]);
    Application::factory()->create([
        'job_id' => $job->id,
        'user_id' => $person->id,
        'cv_document_id' => null,
    ]);

    $this->withToken($company->createToken('applications-index')->plainTextToken)
        ->getJson(route('jobs.applications.index', ['job' => $job->id]))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.data');
});

test('company can update application status for own job', function () {
    $company = createCompanyUser();
    $person = createPersonUser();
    $job = Job::factory()->create(['company_id' => $company->id]);
    $application = Application::factory()->create([
        'job_id' => $job->id,
        'user_id' => $person->id,
        'cv_document_id' => null,
        'reviewed_at' => null,
    ]);

    $this->withToken($company->createToken('applications-update')->plainTextToken)
        ->putJson(route('applications.update', ['application' => $application->id]), [
            'status' => 'under_review',
            'notes' => 'Looks promising.',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'under_review')
        ->assertJsonPath('data.notes', 'Looks promising.');

    expect($application->fresh()->reviewed_at)->not->toBeNull();
});

test('applicant can withdraw before review', function () {
    $company = createCompanyUser();
    $person = createPersonUser();
    $job = Job::factory()->create(['company_id' => $company->id]);
    $application = Application::factory()->create([
        'job_id' => $job->id,
        'user_id' => $person->id,
        'cv_document_id' => null,
        'reviewed_at' => null,
    ]);

    $this->withToken($person->createToken('applications-withdraw')->plainTextToken)
        ->putJson(route('applications.update', ['application' => $application->id]), [
            'status' => 'withdrawn',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.status', 'withdrawn');

    expect($application->fresh()->withdrawn_at)->not->toBeNull();
});

test('unauthorized application access is rejected', function () {
    $company = createCompanyUser();
    $person = createPersonUser();
    $outsider = createPersonUser();
    $job = Job::factory()->create(['company_id' => $company->id]);
    $application = Application::factory()->create([
        'job_id' => $job->id,
        'user_id' => $person->id,
        'cv_document_id' => null,
    ]);

    $this->withToken($outsider->createToken('applications-show-forbidden')->plainTextToken)
        ->getJson(route('applications.show', ['application' => $application->id]))
        ->assertForbidden();
});
