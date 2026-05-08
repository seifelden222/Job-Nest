<?php

use App\Models\Category;
use App\Models\Course;
use App\Models\Job;
use App\Models\Skill;
use App\Models\UserSkill;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('user skill observer syncs a person user to the ai service with exact swagger field names', function () {
    Http::preventStrayRequests();

    Http::fake([
        'https://ai.example.test/api/users/new' => Http::response([
            'user_id' => 4001,
        ], 201),
    ]);

    config()->set('ai.base_url', 'https://ai.example.test');

    $user = createPersonUser(['name' => 'Sarah Backend'], [
        'current_job_title' => 'Backend Developer',
        'employment_type' => 'full_time',
        'preferred_work_location' => 'remote',
        'expected_salary_min' => 10000,
        'expected_salary_max' => 20000,
    ]);

    $skill = Skill::query()->create(['name' => 'Python']);

    UserSkill::query()->create([
        'user_id' => $user->id,
        'skill_id' => $skill->id,
    ]);

    expect($user->fresh()->ai_user_id)->toBe(4001);

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://ai.example.test/api/users/new'
            && $request->data() === [
                'user_name' => 'Sarah Backend',
                'user_skills' => 'Python',
                'role' => 'Backend Developer',
                'user_location' => 'Unknown',
                'experience_years' => 0,
                'preferred_job_type' => 'Remote|Full Time',
                'expected_salary_egp' => '10000-20000',
            ];
    });
});

test('job observer syncs a job to the ai service with exact swagger field names', function () {
    Http::preventStrayRequests();

    Http::fake([
        'https://ai.example.test/api/jobs/new' => Http::response([
            'job_id' => 9001,
        ], 202),
    ]);

    config()->set('ai.base_url', 'https://ai.example.test');

    $company = createCompanyUser(profileAttributes: [
        'company_name' => 'Acme Corp',
    ]);
    $category = Category::query()->create([
        'name' => 'Backend Development',
        'slug' => 'backend-development',
        'type' => 'job',
        'is_active' => true,
    ]);
    $skill = Skill::query()->create(['name' => 'Laravel']);
    fakeContentTranslator();

    $this->withToken($company->createToken('job-sync')->plainTextToken)
        ->postJson(route('jobs.store'), [
            'category_id' => $category->id,
            'industry' => 'FinTech',
            'title' => 'Software Engineer',
            'description' => 'We are looking for...',
            'location' => 'Cairo',
            'employment_type' => 'full_time',
            'salary_min' => 15000,
            'salary_max' => 25000,
            'experience_level' => '2',
            'skill_ids' => [$skill->id],
            'source_language' => 'en',
            'status' => 'active',
        ])
        ->assertCreated();

    $job = Job::query()->latest('id')->firstOrFail();

    expect($job->fresh()->ai_job_id)->toBe(9001);

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://ai.example.test/api/jobs/new'
            && $request->data() === [
                'company_name' => 'Acme Corp',
                'title' => 'Software Engineer',
                'job_required_skills' => 'Laravel',
                'job_location' => 'Cairo',
                'industry' => 'FinTech',
                'job_type' => 'Full Time',
                'salary_range_egp' => '15000-25000',
                'experience_required' => '2',
                'description' => 'We are looking for...',
            ];
    });
});

test('course observer syncs a course to the ai service with exact swagger field names', function () {
    Http::preventStrayRequests();

    Http::fake([
        'https://ai.example.test/api/courses/new' => Http::response([
            'course_id' => 7001,
        ], 201),
    ]);

    config()->set('ai.base_url', 'https://ai.example.test');

    $owner = createPersonUser(['name' => 'Sarah Backend']);
    $category = Category::query()->create([
        'name' => 'Data Science',
        'slug' => 'data-science',
        'type' => 'course',
        'is_active' => true,
    ]);
    $skill = Skill::query()->create(['name' => 'Pandas']);
    fakeContentTranslator();

    $this->withToken($owner->createToken('course-sync')->plainTextToken)
        ->postJson(route('courses.store'), [
            'category_id' => $category->id,
            'title' => 'Python for Everybody',
            'description' => 'Course description',
            'level' => 'beginner',
            'delivery_mode' => 'online',
            'language' => 'ar',
            'price' => 0,
            'duration_hours' => 20,
            'url' => 'https://example.com/course',
            'skill_ids' => [$skill->id],
            'source_language' => 'en',
            'status' => 'published',
        ])
        ->assertCreated();

    $course = Course::query()->latest('id')->firstOrFail();

    expect($course->fresh()->ai_course_id)->toBe(7001);

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://ai.example.test/api/courses/new'
            && $request->data() === [
                'title' => 'Python for Everybody',
                'specialty' => 'Data Science',
                'platform' => 'JobNest',
                'level' => 'Beginner',
                'language' => 'Arabic',
                'price' => 'Free',
                'rating' => 0,
                'skills' => 'Pandas',
                'instructor' => 'Sarah Backend',
                'duration' => '20 hours',
                'certificate' => '',
                'url' => 'https://example.com/course',
            ];
    });
});
