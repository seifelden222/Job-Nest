<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\PortfolioItem;

test('portfolio thumbnail is stored and returned as absolute url', function () {
    Storage::fake('public');

    $user = createPersonUser();
    $token = $user->createToken('portfolio-photo')->plainTextToken;

    $response = $this->withToken($token)->post(route('auth.portfolio.store'), [
        'title' => 'Project with Thumb',
        'thumbnail' => UploadedFile::fake()->image('thumb.jpg'),
    ], ['Accept' => 'application/json']);

    $response->assertCreated();

    $thumbUrl = $response->json('data.thumbnail_url');
    $this->assertIsString($thumbUrl);
    $this->assertStringContainsString('/storage/', $thumbUrl);
    $this->assertTrue(Str::startsWith($thumbUrl, ['http://', 'https://']));

    $item = PortfolioItem::query()->where('user_id', $user->id)->first();
    expect($item)->not->toBeNull();
    expect(Storage::disk('public')->exists($item->thumbnail))->toBeTrue();
    $this->assertStringContainsString($item->thumbnail, $thumbUrl);
});
