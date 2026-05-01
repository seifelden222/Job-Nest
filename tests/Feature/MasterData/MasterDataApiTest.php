<?php

test('admin user can manage skills', function () {
    fakeContentTranslator();

    $user = createAdminUser();
    $token = $user->createToken('manage-skills')->plainTextToken;

    $createResponse = $this->withToken($token)
        ->postJson(route('auth.skills.store'), [
            'name' => 'NestJS',
            'source_language' => 'en',
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
            'source_language' => 'en',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Advanced NestJS');

    $this->withToken($token)
        ->deleteJson(route('auth.skills.destroy', ['skill' => $skillId]))
        ->assertSuccessful();
});

test('admin user can manage languages', function () {
    fakeContentTranslator();

    $user = createAdminUser();
    $token = $user->createToken('manage-languages')->plainTextToken;

    $createResponse = $this->withToken($token)
        ->postJson(route('auth.languages.store'), [
            'name' => 'German',
            'source_language' => 'en',
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
            'source_language' => 'en',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'German B2');

    $this->withToken($token)
        ->deleteJson(route('auth.languages.destroy', ['language' => $languageId]))
        ->assertSuccessful();
});

test('admin user can manage interests', function () {
    fakeContentTranslator();

    $user = createAdminUser();
    $token = $user->createToken('manage-interests')->plainTextToken;

    $createResponse = $this->withToken($token)
        ->postJson(route('auth.interests.store'), [
            'name' => 'Robotics',
            'source_language' => 'en',
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
            'source_language' => 'en',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Industrial Robotics');

    $this->withToken($token)
        ->deleteJson(route('auth.interests.destroy', ['interest' => $interestId]))
        ->assertSuccessful();
});

test('non admin user cannot modify master data', function () {
    fakeContentTranslator();

    $user = createCompanyUser();
    $token = $user->createToken('master-data-404')->plainTextToken;

    $this->withToken($token)
        ->postJson(route('auth.skills.store'), [
            'name' => 'Forbidden Skill',
            'source_language' => 'en',
        ])
        ->assertForbidden();

    $this->withToken($token)
        ->postJson(route('auth.languages.store'), [
            'name' => 'Forbidden Language',
            'source_language' => 'en',
        ])
        ->assertForbidden();

    $this->withToken($token)
        ->postJson(route('auth.interests.store'), [
            'name' => 'Forbidden Interest',
            'source_language' => 'en',
        ])
        ->assertForbidden();
});

test('authenticated user can still read master data', function () {
    fakeContentTranslator();

    $user = createCompanyUser();
    $admin = createAdminUser();
    $token = $user->createToken('master-data-read')->plainTextToken;

    $skillId = $this->withToken($admin->createToken('seed-skill')->plainTextToken)
        ->postJson(route('auth.skills.store'), ['name' => 'NestJS', 'source_language' => 'en'])
        ->json('data.id');

    $languageId = $this->withToken($admin->createToken('seed-language')->plainTextToken)
        ->postJson(route('auth.languages.store'), ['name' => 'German', 'source_language' => 'en'])
        ->json('data.id');

    $interestId = $this->withToken($admin->createToken('seed-interest')->plainTextToken)
        ->postJson(route('auth.interests.store'), ['name' => 'Robotics', 'source_language' => 'en'])
        ->json('data.id');

    $this->withToken($token)
        ->getJson(route('auth.skills.show', ['skill' => $skillId]))
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'NestJS');

    $this->withToken($token)
        ->getJson(route('auth.languages.show', ['language' => $languageId]))
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'German');

    $this->withToken($token)
        ->getJson(route('auth.interests.show', ['interest' => $interestId]))
        ->assertSuccessful()
        ->assertJsonPath('data.name', 'Robotics');
});
