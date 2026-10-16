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

    /**
     * Run configured post-install commands (cache clears, storage link).
     * Failures are reported, never fatal — the lock is still written.
     *
     * @return array<int, array{command: string, ok: bool, output: string}>
     */
    public static function runPostCommands(): array
    {
        $results = [];

        $commands = array_merge(
            (array) config('installer-wizard.post_commands', []),
            config('installer-wizard.storage_link', true) ? ['storage:link'] : []
        );

        foreach ($commands as $command) {
            try {
                $exit = \Illuminate\Support\Facades\Artisan::call($command);
                $results[] = [
                    'command' => $command,
                    'ok' => $exit === 0,
                    'output' => trim(\Illuminate\Support\Facades\Artisan::output()),
                ];
            } catch (\Throwable $e) {
                $results[] = ['command' => $command, 'ok' => false, 'output' => $e->getMessage()];
            }
        }

        return $results;
    }

    /**
     * Ordered wizard steps. License is included only when enabled.
     *
     * @return array<int, string>
     */
    public static function order(): array
    {
        $steps = ['welcome', 'requirements'];

        if (config('installer-wizard.license_enabled')) {
            $steps[] = 'license';
        }

        return array_merge($steps, ['environment', 'database', 'admin', 'complete']);
    }

    public static function routeFor(string $step): string
    {
        return match ($step) {
            'welcome' => 'installer.welcome',
            default => 'installer.'.$step,
        };
    }

    public static function markStep(string $step): void
    {
        $passed = session('installer.passed', []);
        $passed[$step] = true;
        session(['installer.passed' => $passed]);
    }

    public static function passed(string $step): bool
    {
        return (bool) session('installer.passed.'.$step, false);
    }

    /**
     * First step in order without a completion flag, considering only
     * steps BEFORE $upTo. Null = the requested step may open.
     */
    public static function firstIncomplete(?string $upTo = null): ?string
    {
        foreach (static::order() as $step) {
            if ($upTo !== null && $step === $upTo) {
                return null;
            }

            if (! static::passed($step)) {
                return $step;
            }
        }

        return null;
    }
}
