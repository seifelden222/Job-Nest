<?php

use App\Models\Category;
use App\Models\Course;
use App\Models\Skill;

// Store

test('person user can create a course', function () {
    fakeContentTranslator();

    $person = createPersonUser();
    $category = Category::factory()->create(['type' => 'course']);

    $response = $this->withToken($person->createToken('test')->plainTextToken)
        ->postJson(route('courses.store'), [
            'title' => 'Laravel for Beginners',
            'category_id' => $category->id,
            'level' => 'beginner',
            'delivery_mode' => 'online',
            'price' => 0,
            'source_language' => 'en',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.user_id', $person->id)
        ->assertJsonPath('data.title', 'Laravel for Beginners');

    expect(Course::query()->find($response->json('data.id'))?->getTranslations('title'))
        ->toMatchArray(['en' => 'Laravel for Beginners', 'ar' => '[ar]Laravel for Beginners']);
});

test('company user can create a course', function () {
    fakeContentTranslator();

    $company = createCompanyUser();
    $category = Category::factory()->create(['type' => 'course']);

    $response = $this->withToken($company->createToken('test')->plainTextToken)
        ->postJson(route('courses.store'), [
            'title' => 'Company Training Course',
            'category_id' => $category->id,
            'level' => 'intermediate',
            'delivery_mode' => 'hybrid',
            'price' => 999,
            'source_language' => 'en',
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.user_id', $company->id);

    expect(Course::query()->find($response->json('data.id'))?->getTranslations('title'))
        ->toMatchArray(['en' => 'Company Training Course', 'ar' => '[ar]Company Training Course']);
});

test('unauthenticated user cannot create a course', function () {
    $this->postJson(route('courses.store'), [
        'title' => 'Unauthorized Course',
    ])->assertUnauthorized();
});

// Update / Delete

test('owner can update their own course', function () {
    fakeContentTranslator();

    $user = createPersonUser();
    $course = Course::factory()->create(['user_id' => $user->id, 'status' => 'draft']);

    $response = $this->withToken($user->createToken('test')->plainTextToken)
        ->putJson(route('courses.update', $course), [
            'title' => 'Updated Title',
            'source_language' => 'en',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.title', 'Updated Title');

    expect($course->fresh()?->getTranslations('title'))
        ->toMatchArray(['en' => 'Updated Title', 'ar' => '[ar]Updated Title']);
});

test('non-owner cannot update another user course', function () {
    fakeContentTranslator();

    $owner = createPersonUser();
    $other = createPersonUser();
    $course = Course::factory()->create(['user_id' => $owner->id]);

    $this->withToken($other->createToken('test')->plainTextToken)
        ->putJson(route('courses.update', $course), ['title' => 'Hijacked', 'source_language' => 'en'])
        ->assertForbidden();
});

test('owner can delete their own course', function () {
    $user = createPersonUser();
    $course = Course::factory()->create(['user_id' => $user->id]);

    $this->withToken($user->createToken('test')->plainTextToken)
        ->deleteJson(route('courses.destroy', $course))
        ->assertOk();

    $this->assertDatabaseMissing('courses', ['id' => $course->id]);
});

test('non-owner cannot delete another user course', function () {
    $owner = createPersonUser();
    $other = createCompanyUser();
    $course = Course::factory()->create(['user_id' => $owner->id]);

    $this->withToken($other->createToken('test')->plainTextToken)
        ->deleteJson(route('courses.destroy', $course))
        ->assertForbidden();
});

// My Courses

test('my-courses returns only the authenticated user courses', function () {
    $userA = createPersonUser();
    $userB = createCompanyUser();

    Course::factory()->count(3)->create(['user_id' => $userA->id]);
    Course::factory()->count(2)->create(['user_id' => $userB->id]);

    $response = $this->withToken($userA->createToken('test')->plainTextToken)
        ->getJson(route('auth.courses.my'));

    $response->assertOk();
    expect($response->json('data.data'))->toHaveCount(3);
});

// Course listing

test('published courses appear in the public listing', function () {
    $user = createPersonUser();
    Course::factory()->create(['user_id' => $user->id, 'status' => 'published', 'is_active' => true]);
    Course::factory()->create(['user_id' => $user->id, 'status' => 'draft', 'is_active' => false]);

    $response = $this->getJson(route('courses.index'));

    $response->assertOk();
    expect($response->json('data.total'))->toBe(1);
});

// Skills

test('owner can create a course with skills', function () {
    fakeContentTranslator();

    $user = createPersonUser();
    $skill = Skill::create(['name' => 'PHP']);

    $response = $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson(route('courses.store'), [
            'title' => 'PHP Course',
            'price' => 0,
            'skill_ids' => [$skill->id],
            'source_language' => 'en',
        ]);

    $response->assertCreated();
    $this->assertDatabaseHas('course_skills', ['course_id' => $response->json('data.id'), 'skill_id' => $skill->id]);
});
