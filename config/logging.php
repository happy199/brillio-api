<?php

return [

    /*
     |--------------------------------------------------------------------------
     | Default Log Channel
     |--------------------------------------------------------------------------
     |
     | This option defines the default log channel that is utilized to write
     | messages to the logs. The name specified here should match one of
     | the channels defined in the "channels" configuration array.
     |
     */

    'default' => env('LOG_CHANNEL', 'stack'),

    /*
     |--------------------------------------------------------------------------
     | Log Channels
     |--------------------------------------------------------------------------
     |
     | Here you may configure the log channels for your application. Laravel
     | utilizes the Monolog PHP logging library, which includes a variety
     | of powerful log handlers and formatters that you can use.
     |
     | Available Drivers: "single", "daily", "slack", "syslog",
     |                    "errorlog", "monolog", "custom", "stack"
     |
     */

    'channels' => [
        'stack' => [
            'driver' => 'stack',
            'channels' => explode(',', env('LOG_STACK', 'single')),
            'ignore_exceptions' => false,
        ],

        'single' => [
            'driver' => 'single',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'daily' => [
            'driver' => 'daily',
            'path' => storage_path('logs/laravel.log'),
            'level' => env('LOG_LEVEL', 'debug'),
            'days' => env('LOG_DAILY_DAYS', 14),
            'replace_placeholders' => true,
        ],

        'sentry_logs' => [
            'driver' => 'sentry_logs',
            'level' => env('LOG_LEVEL', 'info'),
        ],

        'syslog' => [
            'driver' => 'syslog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'errorlog' => [
            'driver' => 'errorlog',
            'level' => env('LOG_LEVEL', 'debug'),
            'replace_placeholders' => true,
        ],

        'null' => [
            'driver' => 'monolog',
            'handler' => Monolog\Handler\NullHandler::class,
        ],

        'emergency' => [
            'path' => storage_path('logs/laravel.log'),
        ],
    ],

];
