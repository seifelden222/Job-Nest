<?php

use App\Models\Application;
use App\Models\Document;
use App\Models\Job;

test('authenticated user can fetch own applications', function () {
    $company = createCompanyUser();
    $person = createPersonUser();

    $job = Job::factory()->create(['company_id' => $company->id]);
    $cv = Document::factory()->cv()->create(['user_id' => $person->id]);

    $application = Application::factory()->create([
        'job_id' => $job->id,
        'user_id' => $person->id,
        'cv_document_id' => $cv->id,
    ]);

    $this->withToken($person->createToken('my-apps')->plainTextToken)
        ->getJson(route('auth.applications.my'))
        ->assertSuccessful()
        ->assertJsonPath('data.data.0.user_id', $person->id)
        ->assertJsonPath('data.data.0.job_id', $job->id);
});

test('authenticated user does not see others applications', function () {
    $company = createCompanyUser();
    $person = createPersonUser();
    $other = createPersonUser();

    $job = Job::factory()->create(['company_id' => $company->id]);

    Application::factory()->create([
        'job_id' => $job->id,
        'user_id' => $other->id,
    ]);

    $this->withToken($person->createToken('my-apps-empty')->plainTextToken)
        ->getJson(route('auth.applications.my'))
        ->assertSuccessful()
        ->assertJsonCount(0, 'data.data');
});
