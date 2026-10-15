<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Master switch
    |--------------------------------------------------------------------------
    | Set INSTALLER_ENABLED=false in production once installed.
    */
    'enabled' => env('INSTALLER_ENABLED', true),

    'route_prefix' => 'install',

    /*
    |--------------------------------------------------------------------------
    | Installer lock file (checked FIRST, before anything else)
    |--------------------------------------------------------------------------
    | Relative to storage_path() by default -> storage/installer.lock
    | (private, not public). Set an absolute path or INSTALLER_LOCK_FILE
    | env value only if you must store it elsewhere.
    */
    'lock_file' => env('INSTALLER_LOCK_FILE', 'installer.lock'),

    'middleware' => ['web'],

    /*
    | License step is OFF by default. Turn on only if you sell.
    */
    'license_enabled' => env('INSTALLER_LICENSE_ENABLED', false),

    'requirements' => [
        'php' => '8.3.0',
        'extensions' => [
            'pdo', 'mbstring', 'openssl', 'json',
            'tokenizer', 'xml', 'ctype', 'fileinfo',
            'bcmath', 'curl',
        ],
        'permissions' => [
            'storage' => '775',
            'bootstrap/cache' => '775',
        ],
    ],
];
