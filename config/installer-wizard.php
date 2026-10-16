<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Master switch
    |--------------------------------------------------------------------------
    | Set INSTALLER_ENABLED=false in production once installed.
    */
    'enabled' => env('INSTALLER_ENABLED', true),

    /*
    | Branding shown on every wizard page. The package credit line
    | (sbrimon10/laravel-installer-wizard) always stays visible.
    */
    'title' => env('INSTALLER_TITLE', 'Laravel Installer Wizard'),
    'description' => env('INSTALLER_DESCRIPTION', 'Set up your application in a few guided steps.'),

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
    | Verifier class must implement Sbrimon10\InstallerWizard\Support\LicenseVerifier.
    | Use NullVerifier for local dev, EnvatoVerifier for CodeCanyon sales,
    | or bind your own (e.g. a call to your website verify API).
    */
    'license_enabled' => env('INSTALLER_LICENSE_ENABLED', false),
    'license_verifier' => env(
        'INSTALLER_LICENSE_VERIFIER',
        Sbrimon10\InstallerWizard\Support\License\EnvatoVerifier::class
    ),
    'license_envato_token' => env('INSTALLER_ENVATO_TOKEN'),
    'license_env_key' => env('INSTALLER_LICENSE_ENV_KEY', 'APP_PURCHASE_CODE'),

    /*
    | Model used to create the admin user. Defaults to the host
    | auth provider model. Override per project if needed.
    */
    'admin_model' => env('INSTALLER_ADMIN_MODEL', 'App\\Models\\User'),

    // Run `migrate --seed` from the database step when true.
    'run_seed' => env('INSTALLER_RUN_SEED', false),

    // Optional seeder class run after migrations (e.g. Database\Seeders\DatabaseSeeder).
    // Takes precedence over run_seed when set. Null = migrations only / --seed flag.
    'seed_class' => env('INSTALLER_SEED_CLASS'),

    // Artisan commands run at finish, before the lock is written
    // (cache clears so the fresh .env takes effect everywhere).
    'post_commands' => ['config:clear', 'route:clear', 'view:clear'],
    'storage_link' => env('INSTALLER_STORAGE_LINK', true),

    'requirements' => [
        'php' => '8.3.0',
        'extensions' => [
            // Missing required = installer blocks. Dev edits after publishing config.
            'required' => [
                'pdo', 'mbstring', 'openssl', 'json',
                'tokenizer', 'xml', 'ctype', 'fileinfo', 'bcmath',
            ],
            // Missing optional = amber warning only, never blocks.
            // e.g. zip (uploads), gd (images), intl (locales), curl (license API).
            'optional' => ['curl', 'zip', 'gd', 'intl'],
        ],
        'permissions' => [
            'storage' => '775',
            'bootstrap/cache' => '775',
        ],
    ],
];
