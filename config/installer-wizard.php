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
    | When the host app registers RedirectToInstaller in its web group,
    | these paths redirect to the wizard until the lock file exists.
    | Default is just the homepage so the package never hijacks the app.
    */
    'auto_redirect' => env('INSTALLER_AUTO_REDIRECT', true),
    'redirect_paths' => ['/'],

    /*
    | License step is OFF by default. Turn on only if you sell.
    */
    'license_enabled' => env('INSTALLER_LICENSE_ENABLED', false),

    /*
    | Model used to create the admin user. Defaults to the host
    | auth provider model. Override per project if needed.
    */
    'admin_model' => env('INSTALLER_ADMIN_MODEL', 'App\\Models\\User'),

    // Run `migrate --seed` from the database step when true.
    'run_seed' => env('INSTALLER_RUN_SEED', false),

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
