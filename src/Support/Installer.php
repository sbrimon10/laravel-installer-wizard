<?php

namespace Sbrimon10\InstallerWizard\Support;

class Installer
{
    /**
     * Absolute path to the lock file. Defaults to storage_path('installer.lock')
     * (private, NOT public). Override via config 'lock_file' with an
     * absolute path if you really need it elsewhere.
     */
    public static function lockPath(): string
    {
        $file = config('installer-wizard.lock_file', 'installer.lock');

        // Allow absolute path override, otherwise resolve inside storage/.
        if (str_starts_with($file, '/') || preg_match('/^[A-Z]:\\\\/i', $file)) {
            return $file;
        }

        return storage_path(ltrim($file, '/\\'));
    }

    public static function installed(): bool
    {
        return file_exists(static::lockPath());
    }

    public static function markInstalled(string $contents = ''): void
    {
        file_put_contents(static::lockPath(), $contents ?: date('c'));
    }
}
