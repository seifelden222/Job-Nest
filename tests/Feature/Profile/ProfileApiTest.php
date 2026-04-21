<?php

use App\Models\Document;
use App\Models\Interest;
use App\Models\Language;
use App\Models\Skill;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('person can fetch profile', function () {
    $user = createPersonUser();
    $user->skills()->attach(Skill::create(['name' => 'Laravel']));
    $user->languages()->attach(Language::create(['name' => 'English']));
    $user->interests()->attach(Interest::create(['name' => 'AI']));
    Document::factory()->cv()->create(['user_id' => $user->id]);

    $token = $user->createToken('person-profile')->plainTextToken;

    $this->withToken($token)
        ->getJson(route('auth.profile.show'))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonCount(1, 'data.skills')
        ->assertJsonCount(1, 'data.languages')
        ->assertJsonCount(1, 'data.interests')
        ->assertJsonCount(1, 'data.documents');
});

test('person can update profile', function () {
    $user = createPersonUser();
    $token = $user->createToken('person-update')->plainTextToken;

    $this->withToken($token)
        ->putJson(route('auth.profile.update'), [
            'name' => 'Updated Person',
            'phone' => '01011112222',
            'university' => 'Alexandria University',
            'major' => 'Software Engineering',
            'current_job_title' => 'API Developer',
            'linkedin_url' => 'https://linkedin.com/in/updated-person',
            'preferred_work_location' => 'remote',
            'expected_salary_min' => 5000,
            'expected_salary_max' => 9000,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated Person')
        ->assertJsonPath('data.person_profile.university', 'Alexandria University')
        ->assertJsonPath('data.person_profile.current_job_title', 'API Developer');
});

test('company can update profile', function () {
    $user = createCompanyUser();
    $token = $user->createToken('company-update')->plainTextToken;

    $this->withToken($token)
        ->putJson(route('auth.profile.update'), [
            'name' => 'Recruitment Lead',
            'phone' => '01022223333',
            'company_name' => 'JobNest Labs',
            'website' => 'https://jobnest.test',
            'company_size' => '201-500',
            'industry' => 'HR Tech',
            'location' => 'Cairo',
            'about' => 'We build hiring products.',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Recruitment Lead')
        ->assertJsonPath('data.company_profile.company_name', 'JobNest Labs')
        ->assertJsonPath('data.company_profile.industry', 'HR Tech');
});

test('user can list documents', function () {
    $user = createPersonUser();
    Document::factory()->cv()->create(['user_id' => $user->id]);
    Document::factory()->certificate()->create(['user_id' => $user->id]);

    $this->withToken($user->createToken('documents-index')->plainTextToken)
        ->getJson(route('auth.user-documents.index'))
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');
});

test('user can upload cv document', function () {
    Storage::fake('public');
    $user = createPersonUser();

    $response = $this->withToken($user->createToken('documents-store-cv')->plainTextToken)
        ->post(route('auth.user-documents.store'), [
            'type' => 'cv',
            'title' => 'Primary CV',
            'file' => UploadedFile::fake()->create('cv.pdf', 120, 'application/pdf'),
        ], ['Accept' => 'application/json']);

    $response->assertCreated()
        ->assertJsonPath('data.type', 'cv')
        ->assertJsonPath('data.is_primary', true);

    expect(Storage::disk('public')->exists($response->json('data.file_path')))->toBeTrue();
});

test('user can upload certificate document', function () {
    Storage::fake('public');
    $user = createPersonUser();

    $response = $this->withToken($user->createToken('documents-store-cert')->plainTextToken)
        ->post(route('auth.user-documents.store'), [
            'type' => 'certificate',
            'title' => 'AWS Certificate',
            'file' => UploadedFile::fake()->create('certificate.pdf', 150, 'application/pdf'),
        ], ['Accept' => 'application/json']);

    $response->assertCreated()
        ->assertJsonPath('data.type', 'certificate')
        ->assertJsonPath('data.is_primary', false);
});

test('user can delete own document', function () {
    Storage::fake('public');
    $user = createPersonUser();
    $document = Document::factory()->certificate()->create([
        'user_id' => $user->id,
        'file_path' => 'documents/certificate.pdf',
    ]);
    Storage::disk('public')->put($document->file_path, 'fake');

    $this->withToken($user->createToken('documents-delete')->plainTextToken)
        ->deleteJson(route('auth.user-documents.destroy', ['user_document' => $document->id]))
        ->assertSuccessful();

    $this->assertDatabaseMissing('documents', ['id' => $document->id]);
    expect(Storage::disk('public')->exists($document->file_path))->toBeFalse();
});

test('user can list store and delete skills', function () {
    $user = createPersonUser();
    $php = Skill::create(['name' => 'PHP']);
    $laravel = Skill::create(['name' => 'Laravel']);

    $token = $user->createToken('skills-flow')->plainTextToken;

    $this->withToken($token)
        ->postJson(route('auth.user-skills.store'), [
            'skills' => [$php->id, $laravel->id],
        ])
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');

    $this->withToken($token)
        ->getJson(route('auth.user-skills.index'))
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');

    $this->withToken($token)
        ->deleteJson(route('auth.user-skills.destroy', ['user_skill' => $php->id]))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('user can list store and delete languages', function () {
    $user = createPersonUser();
    $english = Language::create(['name' => 'English']);
    $arabic = Language::create(['name' => 'Arabic']);
    $token = $user->createToken('languages-flow')->plainTextToken;

    $this->withToken($token)
        ->postJson(route('auth.user-languages.store'), [
            'language_id' => $english->id,
        ])
        ->assertCreated();

    $this->withToken($token)
        ->postJson(route('auth.user-languages.store'), [
            'language_id' => $arabic->id,
        ])
        ->assertCreated();

    $this->withToken($token)
        ->getJson(route('auth.user-languages.index'))
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');

    $this->withToken($token)
        ->deleteJson(route('auth.user-languages.destroy', ['user_language' => $english->id]))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});

test('user can list store and delete interests', function () {
    $user = createPersonUser();
    $ai = Interest::create(['name' => 'AI']);
    $fintech = Interest::create(['name' => 'Fintech']);
    $token = $user->createToken('interests-flow')->plainTextToken;

    $this->withToken($token)
        ->postJson(route('auth.user-interests.store'), [
            'interests' => [$ai->id, $fintech->id],
        ])
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');

    $this->withToken($token)
        ->getJson(route('auth.user-interests.index'))
        ->assertSuccessful()
        ->assertJsonCount(2, 'data');

    $this->withToken($token)
        ->deleteJson(route('auth.user-interests.destroy', ['user_interest' => $ai->id]))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data');
});
