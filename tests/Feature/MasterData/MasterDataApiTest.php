<?php

test('authenticated user can manage skills', function () {
    $user = createCompanyUser();
    $token = $user->createToken('manage-skills')->plainTextToken;

    $createResponse = $this->withToken($token)
        ->postJson(route('auth.skills.store'), [
            'name' => 'NestJS',
        ]);

    $createResponse->assertCreated()
        ->assertJsonPath('data.name', 'NestJS');

    $skillId = $createResponse->json('data.id');

    $this->withToken($token)
        ->getJson(route('auth.skills.index'))
        ->assertSuccessful();

    $this->withToken($token)
        ->getJson(route('auth.skills.show', ['skill' => $skillId]))
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'NestJS');

    $this->withToken($token)
        ->putJson(route('auth.skills.update', ['skill' => $skillId]), [
            'name' => 'Advanced NestJS',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Advanced NestJS');

    $this->withToken($token)
        ->deleteJson(route('auth.skills.destroy', ['skill' => $skillId]))
        ->assertSuccessful();
});

test('authenticated user can manage languages', function () {
    $user = createCompanyUser();
    $token = $user->createToken('manage-languages')->plainTextToken;

    $createResponse = $this->withToken($token)
        ->postJson(route('auth.languages.store'), [
            'name' => 'German',
        ]);

    $languageId = $createResponse->assertCreated()->json('data.id');

    $this->withToken($token)
        ->getJson(route('auth.languages.index'))
        ->assertSuccessful();

    $this->withToken($token)
        ->getJson(route('auth.languages.show', ['language' => $languageId]))
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'German');

    $this->withToken($token)
        ->putJson(route('auth.languages.update', ['language' => $languageId]), [
            'name' => 'German B2',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'German B2');

    $this->withToken($token)
        ->deleteJson(route('auth.languages.destroy', ['language' => $languageId]))
        ->assertSuccessful();
});

test('authenticated user can manage interests', function () {
    $user = createCompanyUser();
    $token = $user->createToken('manage-interests')->plainTextToken;

    $createResponse = $this->withToken($token)
        ->postJson(route('auth.interests.store'), [
            'name' => 'Robotics',
        ]);

    $interestId = $createResponse->assertCreated()->json('data.id');

    $this->withToken($token)
        ->getJson(route('auth.interests.index'))
        ->assertSuccessful();

    $this->withToken($token)
        ->getJson(route('auth.interests.show', ['interest' => $interestId]))
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Robotics');

    $this->withToken($token)
        ->putJson(route('auth.interests.update', ['interest' => $interestId]), [
            'name' => 'Industrial Robotics',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Industrial Robotics');

    $this->withToken($token)
        ->deleteJson(route('auth.interests.destroy', ['interest' => $interestId]))
        ->assertSuccessful();
});

test('master data endpoints return not found for missing resources', function () {
    $user = createCompanyUser();
    $token = $user->createToken('master-data-404')->plainTextToken;

    $this->withToken($token)
        ->getJson(route('auth.skills.show', ['skill' => 999999]))
        ->assertNotFound();

    $this->withToken($token)
        ->getJson(route('auth.languages.show', ['language' => 999999]))
        ->assertNotFound();

    $this->withToken($token)
        ->getJson(route('auth.interests.show', ['interest' => 999999]))
        ->assertNotFound();
});
