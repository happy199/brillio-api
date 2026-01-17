<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Brillio Custom Services
    |--------------------------------------------------------------------------
    */

    // OpenRouter API - Pour accéder à DeepSeek R1 et autres modèles IA
    'openrouter' => [
        'api_key' => env('OPENROUTER_API_KEY', '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'),
        'api_url' => env('OPENROUTER_API_URL', 'https://openrouter.ai/api/v1/chat/completions'),
        'model' => env('OPENROUTER_MODEL', 'deepseek/deepseek-r1:free'),
        'max_tokens' => env('OPENROUTER_MAX_TOKENS', 2000),
        'temperature' => env('OPENROUTER_TEMPERATURE', 0.7),
        'site_url' => env('OPENROUTER_SITE_URL', 'https://www.brillio.africa'),
        'site_name' => env('OPENROUTER_SITE_NAME', 'Brillio'),
    ],

    // OpenMBTI API - Test de personnalité MBTI gratuit
    // Documentation: https://openmbti.org/api-docs
    'openmbti' => [
        'api_url' => env('OPENMBTI_API_URL', 'https://openmbti.org/api'),
        'locale' => env('OPENMBTI_LOCALE', 'fr'), // Résultats en français
    ],

    // Supabase Authentication
    'supabase' => [
        'url' => env('SUPABASE_URL'),
        'anon_key' => env('SUPABASE_ANON_KEY'),
        'service_role_key' => env('SUPABASE_SERVICE_ROLE_KEY'),
    ],

];
