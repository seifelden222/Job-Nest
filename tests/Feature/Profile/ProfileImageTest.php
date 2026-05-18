<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

test('person can upload profile photo and response contains absolute url', function () {
    Storage::fake('public');

    $user = createPersonUser();
    $token = $user->createToken('profile-photo')->plainTextToken;

    $response = $this->withToken($token)
        ->put(route('auth.profile.update'), [
            'profile_photo' => UploadedFile::fake()->image('photo.jpg'),
            'name' => 'With Photo',
        ], ['Accept' => 'application/json']);

    $response->assertSuccessful();

    $photoUrl = $response->json('data.profile_photo');
    $this->assertIsString($photoUrl);
    $this->assertStringContainsString('/storage/', $photoUrl);
    $this->assertTrue(Str::startsWith($photoUrl, ['http://', 'https://']));

    $path = $user->fresh()->profile_photo;
    expect($path)->not->toBeNull();
    expect(Storage::disk('public')->exists($path))->toBeTrue();
    $this->assertStringContainsString($path, $photoUrl);
});

test('documents urls in profile are absolute', function () {
    Storage::fake('public');

    $user = createPersonUser();

    // create a fake document on disk and a DB record
    $path = 'documents/cv.pdf';
    Storage::disk('public')->put($path, 'dummy');

    \App\Models\Document::factory()->cv()->create([
        'user_id' => $user->id,
        'file_path' => $path,
    ]);

    $token = $user->createToken('documents')->plainTextToken;

    $response = $this->withToken($token)->getJson(route('auth.profile.show'));
    $response->assertSuccessful();

    $docUrl = $response->json('data.documents.0.url');
    $this->assertIsString($docUrl);
    $this->assertStringContainsString('/storage/', $docUrl);
    $this->assertTrue(Str::startsWith($docUrl, ['http://', 'https://']));
    $this->assertStringContainsString($path, $docUrl);
});
