<?php

use App\Models\Category;
use App\Models\Course;

test('api returns only the requested language for translated content', function () {
    fakeContentTranslator();

    $user = createPersonUser();
    $category = Category::factory()->create(['type' => 'course']);

    $courseResponse = $this->withToken($user->createToken('translated-course')->plainTextToken)
        ->postJson(route('courses.store'), [
            'title' => 'Laravel for Teams',
            'description' => 'Practical backend training.',
            'category_id' => $category->id,
            'status' => 'published',
            'is_active' => true,
            'source_language' => 'en',
        ]);

    $courseResponse->assertCreated()
        ->assertJsonPath('data.title', 'Laravel for Teams')
        ->assertJsonPath('data.description', 'Practical backend training.');

    $course = Course::query()->findOrFail($courseResponse->json('data.id'));

    expect($course->getTranslations('title'))
        ->toMatchArray([
            'en' => 'Laravel for Teams',
            'ar' => '[ar]Laravel for Teams',
        ]);

    $this->withHeaders(['Accept-Language' => 'ar'])
        ->getJson(route('courses.show', ['course' => $course->id]))
        ->assertSuccessful()
        ->assertHeader('Content-Language', 'ar')
        ->assertJsonPath('data.title', '[ar]Laravel for Teams')
        ->assertJsonPath('data.description', '[ar]Practical backend training.');
});

test('invalid locale falls back to english and query parameter can set locale', function () {
    fakeContentTranslator();

    $admin = createAdminUser();

    $createResponse = $this->withToken($admin->createToken('translated-category')->plainTextToken)
        ->postJson(route('auth.categories.store'), [
            'name' => 'Engineering',
            'description' => 'Technical roles',
            'type' => 'job',
            'source_language' => 'en',
        ]);

    $categoryId = $createResponse->assertCreated()->json('data.id');

    $this->withHeaders(['Accept-Language' => 'fr'])
        ->getJson(route('categories.show', ['category' => $categoryId]))
        ->assertSuccessful()
        ->assertHeader('Content-Language', 'en')
        ->assertJsonPath('data.name', 'Engineering');

    $this->getJson(route('categories.show', ['category' => $categoryId, 'lang' => 'ar']))
        ->assertSuccessful()
        ->assertHeader('Content-Language', 'ar')
        ->assertJsonPath('data.name', '[ar]Engineering')
        ->assertJsonPath('data.description', '[ar]Technical roles');
});
