<?php

use App\Models\Category;
use App\Models\Course;
use App\Models\Skill;

// Store

test('person user can create a course', function () {
    $person = createPersonUser();
    $category = Category::factory()->create(['type' => 'course']);

    $response = $this->withToken($person->createToken('test')->plainTextToken)
        ->postJson(route('courses.store'), [
            'title' => 'Laravel for Beginners',
            'category_id' => $category->id,
            'level' => 'beginner',
            'delivery_mode' => 'online',
            'price' => 0,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.user_id', $person->id)
        ->assertJsonPath('data.title', 'Laravel for Beginners');

    $this->assertDatabaseHas('courses', ['user_id' => $person->id, 'title' => 'Laravel for Beginners']);
});

test('company user can create a course', function () {
    $company = createCompanyUser();
    $category = Category::factory()->create(['type' => 'course']);

    $response = $this->withToken($company->createToken('test')->plainTextToken)
        ->postJson(route('courses.store'), [
            'title' => 'Company Training Course',
            'category_id' => $category->id,
            'level' => 'intermediate',
            'delivery_mode' => 'hybrid',
            'price' => 999,
        ]);

    $response->assertCreated()
        ->assertJsonPath('data.user_id', $company->id);

    $this->assertDatabaseHas('courses', ['user_id' => $company->id, 'title' => 'Company Training Course']);
});

test('unauthenticated user cannot create a course', function () {
    $this->postJson(route('courses.store'), [
        'title' => 'Unauthorized Course',
    ])->assertUnauthorized();
});

// Update / Delete

test('owner can update their own course', function () {
    $user = createPersonUser();
    $course = Course::factory()->create(['user_id' => $user->id, 'status' => 'draft']);

    $response = $this->withToken($user->createToken('test')->plainTextToken)
        ->putJson(route('courses.update', $course), [
            'title' => 'Updated Title',
        ]);

    $response->assertOk()
        ->assertJsonPath('data.title', 'Updated Title');

    $this->assertDatabaseHas('courses', ['id' => $course->id, 'title' => 'Updated Title']);
});

test('non-owner cannot update another user course', function () {
    $owner = createPersonUser();
    $other = createPersonUser();
    $course = Course::factory()->create(['user_id' => $owner->id]);

    $this->withToken($other->createToken('test')->plainTextToken)
        ->putJson(route('courses.update', $course), ['title' => 'Hijacked'])
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
    $user = createPersonUser();
    $skill = Skill::create(['name' => 'PHP']);

    $response = $this->withToken($user->createToken('test')->plainTextToken)
        ->postJson(route('courses.store'), [
            'title' => 'PHP Course',
            'price' => 0,
            'skill_ids' => [$skill->id],
        ]);

    $response->assertCreated();
    $this->assertDatabaseHas('course_skills', ['course_id' => $response->json('data.id'), 'skill_id' => $skill->id]);
});
