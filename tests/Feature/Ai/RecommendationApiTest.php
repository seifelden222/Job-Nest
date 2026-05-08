<?php

use App\Models\Job;
use App\Models\Skill;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;

test('authenticated user can fetch ai health', function () {
    Http::preventStrayRequests();

    Http::fake([
        'https://ai.example.test/api/health' => Http::response([
            'status' => 'ok',
            'models_loaded' => true,
            'total_jobs' => 1993,
            'total_users' => 15005,
            'total_courses' => 2001,
        ], 200),
    ]);

    config()->set('ai.base_url', 'https://ai.example.test');

    $user = createPersonUser();

    $this->withToken($user->createToken('ai-health')->plainTextToken)
        ->getJson(route('ai.health.show'))
        ->assertOk()
        ->assertJsonPath('data.status', 'ok')
        ->assertJsonPath('data.models_loaded', true);
});

test('authenticated user can fetch ai recommendations using the exact swagger payload fields', function () {
    Http::preventStrayRequests();

    Http::fake([
        'https://ai.example.test/api/recommend' => Http::response([
            'user_id' => 7001,
            'user_name' => 'Sarah Backend',
            'total_results' => 1,
            'recommendations' => [
                [
                    'job_id' => 935,
                    'title' => 'Java Developer',
                    'company_name' => 'Salesforce',
                    'industry' => 'Backend Development',
                    'job_type' => 'Remote',
                    'job_location' => 'Saudi Arabia',
                    'salary_range_egp' => '22000-38000',
                    'experience_required' => '3-7',
                    'job_required_skills' => 'Express.js|Docker|Redis|MongoDB|GraphQL',
                    'content_score' => 0.3718,
                    'ml_score' => 0.0894,
                    'final_score' => 0.2024,
                ],
            ],
        ], 200),
    ]);

    config()->set('ai.base_url', 'https://ai.example.test');

    $user = createPersonUser([
        'name' => 'Sarah Backend',
        'ai_user_id' => 7001,
    ]);

    $this->withToken($user->createToken('ai-recommend')->plainTextToken)
        ->postJson(route('ai.recommendations.store'), [
            'top_n' => 5,
        ])
        ->assertOk()
        ->assertJsonPath('data.user_id', 7001)
        ->assertJsonPath('data.recommendations.0.job_id', 935);

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://ai.example.test/api/recommend'
            && $request->data() === [
                'user_id' => 7001,
                'user_name' => 'Sarah Backend',
                'top_n' => 5,
            ];
    });
});

test('authenticated user can fetch realtime recommendations using the exact swagger payload fields', function () {
    Http::preventStrayRequests();

    Http::fake([
        'https://ai.example.test/api/recommend/realtime' => Http::response([
            'user_id' => 0,
            'user_name' => 'Anonymous',
            'total_results' => 1,
            'recommendations' => [
                [
                    'job_id' => 993,
                    'title' => 'Backend Developer',
                    'company_name' => 'Robusta',
                    'industry' => 'Backend Development',
                    'job_type' => 'Full Time',
                    'job_location' => 'Aswan',
                    'salary_range_egp' => '8000-14000',
                    'experience_required' => '1-3',
                    'job_required_skills' => 'REST API|MongoDB|Express.js|Django',
                    'content_score' => 0.306,
                    'ml_score' => 0.9416,
                    'final_score' => 0.6874,
                ],
            ],
        ], 200),
    ]);

    config()->set('ai.base_url', 'https://ai.example.test');

    $user = createPersonUser(profileAttributes: [
        'about' => 'Backend developer',
        'employment_type' => 'full_time',
        'preferred_work_location' => 'remote',
        'expected_salary_min' => 10000,
        'expected_salary_max' => 20000,
    ]);

    $skill = Skill::query()->create(['name' => 'Python']);
    $user->skills()->attach($skill->id);

    $this->withToken($user->createToken('ai-recommend-realtime')->plainTextToken)
        ->postJson(route('ai.recommendations.realtime'), [
            'top_n' => 2,
        ])
        ->assertOk()
        ->assertJsonPath('data.user_id', 0)
        ->assertJsonPath('data.recommendations.0.job_id', 993);

    Http::assertSent(function (Request $request): bool {
        return $request->url() === 'https://ai.example.test/api/recommend/realtime'
            && $request->data() === [
                'user_skills' => 'Python',
                'cv_summary' => 'Backend developer',
                'user_location' => 'Unknown',
                'experience_years' => 0,
                'preferred_job_type' => 'Remote|Full Time',
                'expected_salary_egp' => '10000-20000',
                'top_n' => 2,
            ];
    });
});

test('authenticated user can fetch ai course recommendations and ai user search', function () {
    Http::preventStrayRequests();

    Http::fake([
        'https://ai.example.test/api/courses/recommend' => Http::response([
            'user_id' => 8001,
            'user_name' => 'Mona Data',
            'user_skills' => 'Python|Docker',
            'total_results' => 1,
            'recommendations' => [
                [
                    'course_id' => 1856,
                    'title' => 'Cloud Diploma',
                    'platform' => 'NTI',
                ],
            ],
        ], 200),
        'https://ai.example.test/api/user/search*' => Http::response([
            'query' => 'Magdy',
            'total_found' => 1,
            'users' => [
                [
                    'user_id' => 1785,
                    'user_name' => 'Magdy Sherif',
                    'role' => 'Student',
                    'user_location' => 'Fayoum',
                    'user_skills' => 'Flutter|SQLite',
                ],
            ],
        ], 200),
    ]);

    config()->set('ai.base_url', 'https://ai.example.test');

    $user = createPersonUser([
        'name' => 'Mona Data',
        'ai_user_id' => 8001,
    ], ['current_job_title' => 'Data Analyst']);

    $this->withToken($user->createToken('ai-courses-recommend')->plainTextToken)
        ->postJson(route('ai.courses.recommend'), [
            'top_n' => 3,
        ])
        ->assertOk()
        ->assertJsonPath('data.recommendations.0.course_id', 1856);

    $this->withToken($user->createToken('ai-user-search')->plainTextToken)
        ->getJson(route('ai.users.search', ['name' => 'Magdy', 'limit' => 1]))
        ->assertOk()
        ->assertJsonPath('data.total_found', 1)
        ->assertJsonPath('data.users.0.user_name', 'Magdy Sherif');
});

test('authenticated user can fetch ai job score using the local job route and stored ai_job_id', function () {
    Http::preventStrayRequests();

    Http::fake([
        'https://ai.example.test/api/jobs/935/score' => Http::response([
            'job_id' => 935,
            'title' => 'Java Developer',
            'company_name' => 'Salesforce',
            'in_live_dataframe' => true,
            'in_tfidf_matrix' => true,
            'matrix_size' => 1993,
        ], 200),
    ]);

    config()->set('ai.base_url', 'https://ai.example.test');

    $user = createCompanyUser();
    $job = Job::factory()->create([
        'company_id' => $user->id,
        'ai_job_id' => 935,
    ]);

    $this->withToken($user->createToken('ai-job-score')->plainTextToken)
        ->getJson(route('ai.jobs.score', ['job' => $job->id]))
        ->assertOk()
        ->assertJsonPath('data.job_id', 935)
        ->assertJsonPath('data.in_live_dataframe', true);
});

test('authenticated user can fetch ai user details and ai courses list through local proxy routes', function () {
    Http::preventStrayRequests();

    Http::fake([
        'https://ai.example.test/api/user/1785' => Http::response([
            'user_id' => 1785,
            'user_name' => 'Magdy Sherif',
            'role' => 'Student',
            'user_skills' => 'Flutter|SQLite',
            'user_location' => 'Fayoum',
            'experience_years' => 0,
            'education' => 'Student - Information Systems',
            'expected_salary_egp' => '4000-7000',
            'preferred_job_type' => 'Hybrid|Remote',
            'cv_summary' => 'Flutter developer with hands-on experience building cross-platform mobile apps.',
            'user_salary_min' => 4000,
            'user_salary_max' => 7000,
        ], 200),
        'https://ai.example.test/api/courses*' => Http::response([
            'total' => 2001,
            'showing' => 1,
            'courses' => [
                [
                    'course_id' => 800,
                    'title' => 'Full Stack Development - 2024 Updated',
                    'platform' => 'edX',
                    'instructor' => 'Harvard University',
                    'specialty' => 'Web Development',
                    'skills' => 'REST API|Git|Node.js',
                    'level' => 'Intermediate',
                    'duration' => '12 weeks',
                    'price' => 'Free',
                    'rating' => 5.0,
                    'language' => 'English',
                    'certificate' => 'Yes',
                    'url' => 'https://www.edx.org/course/full-stack-development',
                ],
            ],
        ], 200),
    ]);

    config()->set('ai.base_url', 'https://ai.example.test');

    $authUser = createPersonUser();
    $targetUser = createPersonUser(['ai_user_id' => 1785]);

    $this->withToken($authUser->createToken('ai-user-show')->plainTextToken)
        ->getJson(route('ai.users.show', ['user' => $targetUser->id]))
        ->assertOk()
        ->assertJsonPath('data.user_id', 1785)
        ->assertJsonPath('data.user_name', 'Magdy Sherif');

    $this->withToken($authUser->createToken('ai-courses-index')->plainTextToken)
        ->getJson(route('ai.courses.index', ['specialty' => 'Web Development', 'limit' => 1]))
        ->assertOk()
        ->assertJsonPath('data.total', 2001)
        ->assertJsonPath('data.courses.0.course_id', 800);
});
