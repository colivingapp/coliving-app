<?php

return [
    'analytics_enabled' => env('ANALYTICS_ENABLED', false),
    'matomo_url' => env('MATOMO_URL', ''),
    'matomo_site_id' => env('MATOMO_SITE_ID', ''),
    'ga_site_id' => env('GA_SITE_ID', ''),
    'example_space_uid' => env('EXAMPLE_SPACE_UID'),
    'google_api_key' => env('GOOGLE_API_KEY'),
    'admins' => array_map('intval', explode(',', env('ADMINS', ''))),
];