<?php

use App\Models\PortfolioItem;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

test('person can create portfolio item', function () {
    Storage::fake('public');

    $user = createPersonUser();

    $payload = [
        'title' => 'My Project',
        'description' => 'An awesome project',
        'project_url' => 'https://example.com',
        'github_url' => 'https://github.com/example',
        'technologies' => ['Laravel', 'PHP'],
        'role' => 'Developer',
        'is_public' => true,
        'thumbnail' => UploadedFile::fake()->image('thumb.jpg'),
    ];

    $response = $this->withToken($user->createToken('portfolio-create')->plainTextToken)
        ->postJson(route('auth.portfolio.store'), $payload, ['Accept' => 'application/json']);

    $response->assertCreated()
        ->assertJsonPath('data.title', 'My Project')
        ->assertJsonPath('data.user_id', $user->id);

    expect(PortfolioItem::query()->where('user_id', $user->id)->count())->toBe(1);
});

test('person can list own portfolio items', function () {
    $user = createPersonUser();

    PortfolioItem::create(['user_id' => $user->id, 'title' => 'One']);

    $this->withToken($user->createToken('portfolio-index')->plainTextToken)
        ->getJson(route('auth.portfolio.index'))
        ->assertSuccessful()
        ->assertJsonPath('data.data.0.title', 'One');
});

test('public can view user portfolio (only public items)', function () {
    $user = createPersonUser();

    PortfolioItem::create(['user_id' => $user->id, 'title' => 'Public', 'is_public' => true]);
    PortfolioItem::create(['user_id' => $user->id, 'title' => 'Private', 'is_public' => false]);

    $this->getJson(route('users.portfolio.index', ['user' => $user->id]))
        ->assertSuccessful()
        ->assertJsonCount(1, 'data.data')
        ->assertJsonPath('data.data.0.title', 'Public');
});

test('person can update own portfolio item', function () {
    $user = createPersonUser();

    $item = PortfolioItem::create(['user_id' => $user->id, 'title' => 'Old Title']);

    $this->withToken($user->createToken('portfolio-update')->plainTextToken)
        ->putJson(route('auth.portfolio.update', ['portfolio' => $item->id]), [
            'title' => 'New Title',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.title', 'New Title');
});

test('person can delete own portfolio item', function () {
    $user = createPersonUser();
    $item = PortfolioItem::create(['user_id' => $user->id, 'title' => 'To Delete']);

    $this->withToken($user->createToken('portfolio-delete')->plainTextToken)
        ->deleteJson(route('auth.portfolio.destroy', ['portfolio' => $item->id]))
        ->assertSuccessful();

    $this->assertDatabaseMissing('portfolio_items', ['id' => $item->id]);
});
