<?php

namespace Sbrimon10\InstallerWizard\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Sbrimon10\InstallerWizard\Support\AdminManager;
use Sbrimon10\InstallerWizard\Support\DatabaseManager;
use Sbrimon10\InstallerWizard\Support\EnvManager;
use Sbrimon10\InstallerWizard\Support\Installer;
use Sbrimon10\InstallerWizard\Support\RequirementsChecker;

class InstallerController
{
    public function welcome(): View
    {
        Installer::markStep('welcome');

        return view('installer-wizard::steps.welcome');
    }

    public function requirements(RequirementsChecker $checker): View
    {
        $checks = $checker->check();

        if ($checks['passes']) {
            Installer::markStep('requirements');
        }

        return view('installer-wizard::steps.requirements', [
            'checks' => $checks,
        ]);
    }

    public function license(): View|RedirectResponse
    {
        if (! config('installer-wizard.license_enabled')) {
            return redirect()->route('installer.environment');
        }

        return view('installer-wizard::steps.license');
    }

    public function verifyLicense(Request $request, EnvManager $env): RedirectResponse
    {
        if (! config('installer-wizard.license_enabled')) {
            return redirect()->route('installer.environment');
        }

        $validated = $request->validate([
            'license_code' => ['required', 'string', 'max:255'],
        ]);

        $verifierClass = config('installer-wizard.license_verifier');
        $verifier = app($verifierClass);

        $result = $verifier->verify($validated['license_code']);

        if (! $result['ok']) {
            return back()->withErrors(['license_code' => $result['message']])->withInput();
        }

        session(['installer.license_code' => $validated['license_code']]);
        Installer::markStep('license');

        $env->update([
            config('installer-wizard.license_env_key', 'APP_PURCHASE_CODE') => $validated['license_code'],
        ]);

        return redirect()->route('installer.environment')->with('status', $result['message']);
    }

    public function environment(EnvManager $env): View
    {
        $current = $env->get([
            'APP_NAME', 'APP_URL',
            'DB_CONNECTION', 'DB_HOST', 'DB_PORT',
            'DB_DATABASE', 'DB_USERNAME', 'DB_PASSWORD',
        ]);

        $defaults = [
            'APP_NAME' => config('app.name'),
            'APP_URL' => config('app.url'),
            'DB_CONNECTION' => config('database.default'),
            'DB_HOST' => config('database.connections.mysql.host'),
            'DB_PORT' => config('database.connections.mysql.port'),
            'DB_DATABASE' => '',
            'DB_USERNAME' => '',
            'DB_PASSWORD' => '',
        ];

        return view('installer-wizard::steps.environment', [
            'values' => array_merge($defaults, array_filter($current, fn ($v) => $v !== null)),
        ]);
    }

    public function saveEnvironment(Request $request, EnvManager $env, DatabaseManager $db): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $this->validateEnvironment($request);
        $normalized = $this->normalizeEnvironment($validated);

        $check = $db->testConnection($normalized['params']);

        if (! $check['ok']) {
            if ($request->wantsJson()) {
                return response()->json(['ok' => false, 'message' => $check['message']], 422);
            }

            return back()->withErrors(['DB_CONNECTION' => $check['message']])->withInput();
        }

        $env->update($normalized['env']);
        Installer::markStep('environment');

        // Fresh .env must take effect on the very next request.
        try {
            Artisan::call('config:clear');
        } catch (\Throwable) {
            // Non-fatal: worst case the next boot still reads .env directly.
        }

        if ($request->wantsJson()) {
            return response()->json([
                'ok' => true,
                'message' => 'Environment saved and database connected.',
                'redirect' => route('installer.database'),
            ]);
        }

        return redirect()->route('installer.database')->with('status', 'Environment saved and database connected.');
    }

    /**
     * Live probe WITHOUT writing .env (no server restart, no page reload).
     * Always JSON: {ok, message}. Validation failures stay 422 JSON.
     */
    public function testConnection(Request $request, DatabaseManager $db): \Illuminate\Http\JsonResponse
    {
        $validated = $this->validateEnvironment($request);
        $normalized = $this->normalizeEnvironment($validated);

        $check = $db->testConnection($normalized['params']);

        return response()->json($check, $check['ok'] ? 200 : 422);
    }

    /**
     * Lightweight JSON for clients recovering from a dropped connection
     * (e.g. dev-server restart after .env write). No DB touched.
     */
    public function status(): \Illuminate\Http\JsonResponse
    {
        $next = Installer::firstIncomplete();

        return response()->json([
            'installed' => Installer::installed(),
            'passed' => array_keys(array_filter(session('installer.passed', []))),
            'next' => $next ? route(Installer::routeFor($next)) : null,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validateEnvironment(Request $request): array
    {
        return $request->validate([
            'APP_NAME' => ['required', 'string', 'max:255'],
            'APP_URL' => ['required', 'url', 'max:255'],
            'DB_CONNECTION' => ['required', 'in:mysql,mariadb,pgsql,sqlite'],
            'DB_HOST' => ['required_unless:DB_CONNECTION,sqlite', 'nullable', 'string', 'max:255'],
            'DB_PORT' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'DB_DATABASE' => ['required', 'string', 'max:255'],
            'DB_USERNAME' => ['required_unless:DB_CONNECTION,sqlite', 'nullable', 'string', 'max:255'],
            'DB_PASSWORD' => ['nullable', 'string', 'max:255'],
        ]);
    }

    /**
     * @return array{params: array, env: array}
     */
    protected function normalizeEnvironment(array $validated): array
    {
        $connection = strtolower($validated['DB_CONNECTION']);
        $connection = $connection === 'mariadb' ? 'mysql' : $connection;

        $database = $validated['DB_DATABASE'];

        // SQLite via Laravel requires an absolute path to an existing file
        // (or :memory:). Normalize bare names and create the file so the
        // saved .env can never stick the installer on next boot.
        if ($connection === 'sqlite' && $database !== ':memory:') {
            if (! preg_match('/^([A-Z]:\\\\|\\/)/i', $database)) {
                $database = database_path(preg_match('/\\.sqlite$/i', $database) ? $database : $database.'.sqlite');
            }

            if (! file_exists($database)) {
                @touch($database);
            }
        }

        return [
            'params' => [
                'connection' => $connection,
                'host' => $validated['DB_HOST'] ?? null,
                'port' => $validated['DB_PORT'] ?? null,
                'database' => $database,
                'username' => $validated['DB_USERNAME'] ?? null,
                'password' => $validated['DB_PASSWORD'] ?? null,
            ],
            'env' => [
                'APP_NAME' => $validated['APP_NAME'],
                'APP_URL' => rtrim($validated['APP_URL'], '/'),
                'DB_CONNECTION' => $connection,
                'DB_HOST' => $validated['DB_HOST'] ?? '',
                'DB_PORT' => (string) ($validated['DB_PORT'] ?? ''),
                'DB_DATABASE' => $database,
                'DB_USERNAME' => $validated['DB_USERNAME'] ?? '',
                'DB_PASSWORD' => $validated['DB_PASSWORD'] ?? '',
            ],
        ];
    }

    public function database(DatabaseManager $db): View
    {
        // Warn (don't crash) when the saved .env connection is unreachable,
        // so buyers know to go back to Env instead of staring at a 500.
        $current = config('database.connections.'.config('database.default'));
        $reachable = $db->testConnection([
            'connection' => $current['driver'] ?? config('database.default'),
            'host' => $current['host'] ?? null,
            'port' => $current['port'] ?? null,
            'database' => $current['database'] ?? null,
            'username' => $current['username'] ?? null,
            'password' => $current['password'] ?? null,
        ]);

        return view('installer-wizard::steps.database', [
            'seed' => (bool) config('installer-wizard.run_seed', false),
            'reachable' => $reachable,
        ]);
    }

    public function runMigrations(Request $request, DatabaseManager $db): RedirectResponse
    {
        $validated = $request->validate([
            'fresh' => ['nullable', 'boolean'],
        ]);

        $result = $db->migrate(
            fresh: (bool) ($validated['fresh'] ?? false),
        );

        if (! $result['ok']) {
            return back()->withErrors(['migrate' => $result['message']])->with('migrate_output', $result['output']);
        }

        Installer::markStep('database');

        return redirect()->route('installer.admin')
            ->with('status', $result['message'])
            ->with('migrate_output', $result['output']);
    }

    public function admin(): View
    {
        return view('installer-wizard::steps.admin');
    }

    public function storeAdmin(Request $request, AdminManager $admin): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $result = $admin->create($validated);

        if (! $result['ok']) {
            return back()->withErrors(['email' => $result['message']])->withInput();
        }

        Installer::markStep('admin');

        return redirect()->route('installer.complete')->with('status', $result['message'].' Continue to finish.');
    }

    public function complete(): View
    {
        $installed = Installer::installed();

        return view('installer-wizard::steps.complete', compact('installed'));
    }

    public function finish(): RedirectResponse
    {
        $post = Installer::runPostCommands();
        $failed = array_column(array_filter($post, fn ($r) => ! $r['ok']), 'command');

        if (! Installer::installed()) {
            Installer::markInstalled();
        }

        Installer::markStep('complete');

        $message = 'Installation complete. Wizard is now locked.';

        if (count($failed)) {
            $message .= ' Post-install warnings: '.implode(', ', $failed).' — run them manually via artisan.';
        }

        return redirect()->route('installer.complete')->with('status', $message);
    }
}
