<?php

return [
    'base_url' => rtrim((string) env('AI_BASE_URL', env('AI_API_URL', '')), '/'),
    'timeout' => 15,
    'connect_timeout' => 5,
    'retry_attempts' => 2,
    'retry_sleep' => 250,
    'endpoints' => [
        'health' => '/api/health',
        'recommend' => '/api/recommend',
        'recommend_realtime' => '/api/recommend/realtime',
        'chat' => '/api/chat',
        'jobs' => '/api/jobs',
        'jobs_new' => '/api/jobs/new',
        'job_score' => '/api/jobs/%s/score',
        'user_search' => '/api/user/search',
        'user_show' => '/api/user/%s',
        'users_new' => '/api/users/new',
        'courses' => '/api/courses',
        'courses_recommend' => '/api/courses/recommend',
        'courses_new' => '/api/courses/new',
    ],
];
