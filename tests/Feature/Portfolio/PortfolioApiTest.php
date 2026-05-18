<?php

use App\Models\Application;
use App\Models\Job;
use App\Models\PortfolioItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

// ─────────────────────────────────────────────
// Portfolio CRUD
// ─────────────────────────────────────────────

test('person can list all portfolio items publicly', function () {
    $person = createPersonUser();
    PortfolioItem::factory()->count(3)->create(['user_id' => $person->id]);

    $this->getJson(route('portfolio.index'))
        ->assertSuccessful()
        ->assertJsonCount(3, 'data.data');
});

test('person can create a portfolio item', function () {
    Storage::fake('public');
    $person = createPersonUser();
    $token = $person->createToken('portfolio-store')->plainTextToken;

    $this->withToken($token)
        ->postJson(route('auth.portfolio.store'), [
            'title' => 'My First Project',
            'description' => 'A cool project I built.',
            'live_url' => 'https://example.com',
            'status' => 'completed',
        ])
        ->assertCreated()
        ->assertJsonPath('data.title', 'My First Project')
        ->assertJsonPath('data.status', 'completed')
        ->assertJsonPath('data.user_id', $person->id);

    $this->assertDatabaseHas('portfolio_items', [
        'user_id' => $person->id,
        'title' => 'My First Project',
    ]);
});

test('person can create a portfolio item with image upload', function () {
    Storage::fake('public');
    $person = createPersonUser();
    $token = $person->createToken('portfolio-store-image')->plainTextToken;

    $response = $this->withToken($token)
        ->post(route('auth.portfolio.store'), [
            'title' => 'Project With Image',
            'image' => UploadedFile::fake()->image('project.png', 800, 600),
        ], ['Accept' => 'application/json'])
        ->assertCreated();

    $imagePath = $response->json('data.image_url');
    expect($imagePath)->not->toBeNull();
});

test('person can view a single portfolio item', function () {
    $person = createPersonUser();
    $item = PortfolioItem::factory()->create(['user_id' => $person->id]);

    $this->getJson(route('portfolio.show', $item))
        ->assertSuccessful()
        ->assertJsonPath('data.id', $item->id);
});

test('person can update own portfolio item', function () {
    $person = createPersonUser();
    $item = PortfolioItem::factory()->create(['user_id' => $person->id]);
    $token = $person->createToken('portfolio-update')->plainTextToken;

    $this->withToken($token)
        ->putJson(route('auth.portfolio.update', $item), [
            'title' => 'Updated Title',
            'status' => 'archived',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'Updated Title')
        ->assertJsonPath('data.status', 'archived');
});

test('person cannot update another users portfolio item', function () {
    $owner = createPersonUser();
    $outsider = createPersonUser();
    $item = PortfolioItem::factory()->create(['user_id' => $owner->id]);
    $token = $outsider->createToken('portfolio-update-foreign')->plainTextToken;

    $this->withToken($token)
        ->putJson(route('auth.portfolio.update', $item), [
            'title' => 'Hacked Title',
        ])
        ->assertForbidden();
});

test('person can delete own portfolio item', function () {
    $person = createPersonUser();
    $item = PortfolioItem::factory()->create(['user_id' => $person->id]);
    $token = $person->createToken('portfolio-delete')->plainTextToken;

    $this->withToken($token)
        ->deleteJson(route('auth.portfolio.destroy', $item))
        ->assertSuccessful();

    $this->assertDatabaseMissing('portfolio_items', ['id' => $item->id]);
});

test('company cannot create portfolio items', function () {
    $company = createCompanyUser();
    $token = $company->createToken('portfolio-company-create')->plainTextToken;

    $this->withToken($token)
        ->postJson(route('auth.portfolio.store'), [
            'title' => 'Company Project',
        ])
        ->assertForbidden();
});

test('unauthenticated user cannot create portfolio items', function () {
    $this->postJson(route('auth.portfolio.store'), ['title' => 'Test'])
        ->assertUnauthorized();
});

test('person can list own portfolio via my-portfolio endpoint', function () {
    $person = createPersonUser();
    PortfolioItem::factory()->count(2)->create(['user_id' => $person->id]);
    $token = $person->createToken('my-portfolio')->plainTextToken;

    $this->withToken($token)
        ->getJson(route('auth.portfolio.my'))
        ->assertSuccessful()
        ->assertJsonCount(2, 'data.data');
});

// ─────────────────────────────────────────────
// My Applications endpoint
// ─────────────────────────────────────────────

test('person can list own applications via my-applications endpoint', function () {
    $company = createCompanyUser();
    $person = createPersonUser();
    $job = Job::factory()->create(['company_id' => $company->id]);
    Application::factory()->create([
        'job_id' => $job->id,
        'user_id' => $person->id,
        'cv_document_id' => null,
    ]);

    $token = $person->createToken('my-apps')->plainTextToken;

    $this->withToken($token)
        ->getJson(route('auth.my-applications'))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.data')
        ->assertJsonPath('data.data.0.user_id', $person->id);
});

test('my-applications does not return other users applications', function () {
    $company = createCompanyUser();
    $personA = createPersonUser();
    $personB = createPersonUser();
    $job = Job::factory()->create(['company_id' => $company->id]);
    Application::factory()->create([
        'job_id' => $job->id,
        'user_id' => $personA->id,
        'cv_document_id' => null,
    ]);

    $token = $personB->createToken('my-apps-isolation')->plainTextToken;

    $this->withToken($token)
        ->getJson(route('auth.my-applications'))
        ->assertSuccessful()
        ->assertJsonCount(0, 'data.data');
});

test('my-applications includes job relation', function () {
    $company = createCompanyUser();
    $person = createPersonUser();
    $job = Job::factory()->create(['company_id' => $company->id]);
    Application::factory()->create([
        'job_id' => $job->id,
        'user_id' => $person->id,
        'cv_document_id' => null,
    ]);

    $this->withToken($person->createToken('apps-relations')->plainTextToken)
        ->getJson(route('auth.my-applications'))
        ->assertSuccessful()
        ->assertJsonPath('data.data.0.job.id', $job->id);
});

test('unauthenticated user cannot access my-applications', function () {
    $this->getJson(route('auth.my-applications'))
        ->assertUnauthorized();
});

// ─────────────────────────────────────────────
// Profile photo URL response
// ─────────────────────────────────────────────

test('profile update with photo returns usable public image URL', function () {
    Storage::fake('public');
    $person = createPersonUser();
    $token = $person->createToken('profile-photo-update')->plainTextToken;

    $response = $this->withToken($token)
        ->post(route('auth.profile.update'), [
            'profile_photo' => UploadedFile::fake()->image('avatar.jpg', 200, 200),
        ], ['Accept' => 'application/json'])
        ->assertSuccessful();

    $photoUrl = $response->json('data.profile_photo');

    expect($photoUrl)->not->toBeNull();
    expect($photoUrl)->toContain('profile-photos');
    // Must be a full URL path that Flutter can use, not a raw storage path
    expect($photoUrl)->toStartWith('http');
});

test('profile update without photo does not break profile_photo field', function () {
    $person = createPersonUser();
    $token = $person->createToken('profile-no-photo')->plainTextToken;

    $this->withToken($token)
        ->putJson(route('auth.profile.update'), [
            'name' => 'Updated Name',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Updated Name');
});
