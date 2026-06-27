<?php

return [
    /*
     * |--------------------------------------------------------------------------
     * | Would you like the install button to appear on all pages?
     * Set true/false
     * |--------------------------------------------------------------------------
     */

    'install-button' => false,

    /*
     |--------------------------------------------------------------------------
     | PWA Manifest Configuration
     |--------------------------------------------------------------------------
     |  php artisan erag:update-manifest
     */

    'manifest' => [
        'name' => env('APP_NAME'),
        'short_name' => env('APP_NAME'),
        'background_color' => '#09090b',
        'display' => 'standalone',
        'description' => 'Seu palpite, sua vitória.',
        'theme_color' => '#09090b',
        'icons' => [
            [
                'src' => 'logo.png',
                'sizes' => '512x512',
                'type' => 'image/png',
            ],
        ],
    ],

    /*
     |--------------------------------------------------------------------------
     | Debug Configuration
     |--------------------------------------------------------------------------
     | Toggles the application's debug mode based on the environment variable
     */

    'debug' => env('APP_DEBUG', false),

    /*
     |--------------------------------------------------------------------------
     | Livewire Integration
     |--------------------------------------------------------------------------
     | Set to true if you're using Livewire in your application to enable
     | Livewire-specific PWA optimizations or features.
     */

    'livewire-app' => true,
];
