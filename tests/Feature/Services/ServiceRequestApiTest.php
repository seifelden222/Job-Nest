<?php

use App\Models\Category;
use App\Models\ServiceRequest;

// Store

test('person can create a service request', function () {
    fakeContentTranslator();

    $person = createPersonUser();
    $category = Category::factory()->create(['type' => 'service']);

    $response = $this->withToken($person->createToken('test')->plainTextToken)
        ->postJson(route('service-requests.store'), [
            'title' => 'Need UI design help',
            'description' => 'Landing page redesign',
            'category_id' => $category->id,
            'delivery_mode' => 'online',
            'source_language' => 'en',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.user_id', $person->id);

    expect(ServiceRequest::query()->find($response->json('data.id'))?->getTranslations('title'))
        ->toMatchArray(['en' => 'Need UI design help', 'ar' => '[ar]Need UI design help']);
});

test('company can create a service request', function () {
    fakeContentTranslator();

    $company = createCompanyUser();
    $category = Category::factory()->create(['type' => 'service']);

    $response = $this->withToken($company->createToken('test')->plainTextToken)
        ->postJson(route('service-requests.store'), [
            'title' => 'Need API integration',
            'description' => 'Integrate payment gateway',
            'category_id' => $category->id,
            'delivery_mode' => 'hybrid',
            'source_language' => 'en',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.user_id', $company->id);
});

// Update / Delete ownership

test('owner can update own service request', function () {
    fakeContentTranslator();

    $owner = createPersonUser();

    $serviceRequest = ServiceRequest::factory()->create([
        'user_id' => $owner->id,
        'status' => 'open',
    ]);

    $this->withToken($owner->createToken('test')->plainTextToken)
        ->putJson(route('service-requests.update', $serviceRequest), [
            'title' => 'Updated Title',
            'source_language' => 'en',
        ])
        ->assertOk()
        ->assertJsonPath('data.title', 'Updated Title');
});

test('non-owner cannot update another user service request', function () {
    fakeContentTranslator();

    $owner = createPersonUser();
    $other = createCompanyUser();

    $serviceRequest = ServiceRequest::factory()->create([
        'user_id' => $owner->id,
        'status' => 'open',
    ]);

    $this->withToken($other->createToken('test')->plainTextToken)
        ->putJson(route('service-requests.update', $serviceRequest), [
            'title' => 'Hijacked Title',
            'source_language' => 'en',
        ])
        ->assertForbidden();
});

test('owner can delete own service request', function () {
    $owner = createCompanyUser();

    $serviceRequest = ServiceRequest::factory()->create([
        'user_id' => $owner->id,
        'status' => 'open',
    ]);

    $this->withToken($owner->createToken('test')->plainTextToken)
        ->deleteJson(route('service-requests.destroy', $serviceRequest))
        ->assertOk();

    $this->assertDatabaseMissing('service_requests', ['id' => $serviceRequest->id]);
});

test('non-owner cannot delete another user service request', function () {
    $owner = createCompanyUser();
    $other = createPersonUser();

    $serviceRequest = ServiceRequest::factory()->create([
        'user_id' => $owner->id,
        'status' => 'open',
    ]);

    $this->withToken($other->createToken('test')->plainTextToken)
        ->deleteJson(route('service-requests.destroy', $serviceRequest))
        ->assertForbidden();
});
