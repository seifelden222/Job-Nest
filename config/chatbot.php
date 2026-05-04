<?php

return [
    'provider' => env('CHATBOT_AI_PROVIDER', 'external-ai'),
    'model' => env('CHATBOT_AI_MODEL', 'jobnest-assistant'),
    'base_url' => env('CHATBOT_AI_BASE_URL', ''),
    'path' => env('CHATBOT_AI_PATH', '/chatbot/respond'),
    'system_prompt' => env(
        'CHATBOT_AI_SYSTEM_PROMPT',
        'You are the JobNest assistant. Help users with jobs, courses, service requests, profiles, notifications, and saved items in a concise and practical way.',
    ),
    'history_limit' => (int) env('CHATBOT_HISTORY_LIMIT', 8),
    'timeout' => (int) env('CHATBOT_AI_TIMEOUT', 15),
    'connect_timeout' => (int) env('CHATBOT_AI_CONNECT_TIMEOUT', 5),
    'retry_attempts' => (int) env('CHATBOT_AI_RETRY_ATTEMPTS', 2),
    'retry_sleep' => (int) env('CHATBOT_AI_RETRY_SLEEP', 250),
];
