<?php

use App\Models\Job;
use App\Models\Skill;

test('company can create job with skills', function () {
    fakeContentTranslator();

    $company = createCompanyUser();
    $skillA = Skill::create(['name' => 'PHP']);
    $skillB = Skill::create(['name' => 'Laravel']);

    $response = $this->withToken($company->createToken('company-job-store')->plainTextToken)
        ->postJson(route('jobs.store'), [
            'title' => 'Backend Engineer',
            'description' => 'Build and maintain APIs.',
            'location' => 'Cairo',
            'employment_type' => 'full_time',
            'salary_min' => 8000,
            'salary_max' => 12000,
            'currency' => 'EGP',
            'experience_level' => 'mid',
            'requirements' => 'Laravel and MySQL.',
            'responsibilities' => 'Own the API lifecycle.',
            'deadline' => now()->addWeek()->toDateString(),
            'status' => 'active',
            'skill_ids' => [$skillA->id, $skillB->id],
            'source_language' => 'en',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.company_id', $company->id)
        ->assertJsonCount(2, 'data.skills');

    $jobId = $response->json('data.id');
    $this->assertDatabaseHas('job_skills', ['job_id' => $jobId, 'skill_id' => $skillA->id]);
    $this->assertDatabaseHas('job_skills', ['job_id' => $jobId, 'skill_id' => $skillB->id]);
});

test('non company cannot create job', function () {
    fakeContentTranslator();

    $person = createPersonUser();

    $this->withToken($person->createToken('person-job-store')->plainTextToken)
        ->postJson(route('jobs.store'), [
            'title' => 'Forbidden Job',
            'description' => 'Should not be created.',
            'source_language' => 'en',
        ])
        ->assertForbidden();
});

test('jobs list returns active jobs and supports filters', function () {
    $php = Skill::create(['name' => 'PHP']);
    $matchingJob = Job::factory()->create([
        'title' => 'PHP Backend Engineer',
        'description' => 'Laravel APIs',
        'location' => 'Cairo',
        'employment_type' => 'full_time',
        'status' => 'active',
        'is_active' => true,
    ]);
    $matchingJob->skills()->attach($php->id);

    Job::factory()->draft()->create([
        'title' => 'Draft Job',
        'description' => 'Should not appear publicly.',
    ]);

    $this->getJson(route('jobs.index', [
        'q' => 'Backend',
        'location' => 'Cairo',
        'employment_type' => 'full_time',
        'skill_id' => $php->id,
    ]))
        ->assertSuccessful()
        ->assertJsonPath('data.data.0.id', $matchingJob->id);
});

test('public can show active job', function () {
    $job = Job::factory()->create();

    $this->getJson(route('jobs.show', ['job' => $job->id]))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $job->id);
});

test('non owner cannot view inactive job', function () {
    $job = Job::factory()->draft()->create();

    $this->getJson(route('jobs.show', ['job' => $job->id]))
        ->assertNotFound();
});

test('owner can update own job and sync skills', function () {
    fakeContentTranslator();

    $company = createCompanyUser();
    $job = Job::factory()->create(['company_id' => $company->id]);
    $newSkill = Skill::create(['name' => 'Docker']);

    $this->withToken($company->createToken('job-update')->plainTextToken)
        ->putJson(route('jobs.update', ['job' => $job->id]), [
            'title' => 'Senior Backend Engineer',
            'status' => 'active',
            'skill_ids' => [$newSkill->id],
            'source_language' => 'en',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'Senior Backend Engineer')
        ->assertJsonCount(1, 'data.skills');

    $this->assertDatabaseHas('job_skills', ['job_id' => $job->id, 'skill_id' => $newSkill->id]);
});

test('company cannot update another company job', function () {
    fakeContentTranslator();

    $owner = createCompanyUser();
    $otherCompany = createCompanyUser();
    $job = Job::factory()->create(['company_id' => $owner->id]);

    $this->withToken($otherCompany->createToken('job-update-forbidden')->plainTextToken)
        ->putJson(route('jobs.update', ['job' => $job->id]), [
            'title' => 'Hijacked Title',
            'source_language' => 'en',
        ])
        ->assertForbidden();
});

test('company can delete own job', function () {
    $company = createCompanyUser();
    $job = Job::factory()->create(['company_id' => $company->id]);

    $this->withToken($company->createToken('job-delete')->plainTextToken)
        ->deleteJson(route('jobs.destroy', ['job' => $job->id]))
        ->assertSuccessful();

    $this->assertDatabaseMissing('jobs', ['id' => $job->id]);
});
