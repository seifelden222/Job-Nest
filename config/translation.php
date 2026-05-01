<?php

return [
    'supported_locales' => ['ar', 'en'],
    'default_locale' => 'en',
    'driver' => env('CONTENT_TRANSLATION_DRIVER', 'fallback'),
    'drivers' => [
        'libretranslate' => [
            'base_url' => env('LIBRETRANSLATE_BASE_URL'),
            'api_key' => env('LIBRETRANSLATE_API_KEY'),
            'timeout' => (int) env('LIBRETRANSLATE_TIMEOUT', 10),
        ],
    ],
];
