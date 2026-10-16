<?php

namespace Sbrimon10\InstallerWizard\Http\Controllers;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function saveEnvironment(Request $request, EnvManager $env, DatabaseManager $db): RedirectResponse
    {
        $validated = $request->validate([
            'APP_NAME' => ['required', 'string', 'max:255'],
            'APP_URL' => ['required', 'url', 'max:255'],
            'DB_CONNECTION' => ['required', 'in:mysql,mariadb,pgsql,sqlite'],
            'DB_HOST' => ['required_unless:DB_CONNECTION,sqlite', 'nullable', 'string', 'max:255'],
            'DB_PORT' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'DB_DATABASE' => ['required', 'string', 'max:255'],
            'DB_USERNAME' => ['required_unless:DB_CONNECTION,sqlite', 'nullable', 'string', 'max:255'],
            'DB_PASSWORD' => ['nullable', 'string', 'max:255'],
        ]);

        $connection = strtolower($validated['DB_CONNECTION']);
        $connection = $connection === 'mariadb' ? 'mysql' : $connection;

        $check = $db->testConnection([
            'connection' => $connection,
            'host' => $validated['DB_HOST'] ?? null,
            'port' => $validated['DB_PORT'] ?? null,
            'database' => $validated['DB_DATABASE'],
            'username' => $validated['DB_USERNAME'] ?? null,
            'password' => $validated['DB_PASSWORD'] ?? null,
        ]);

        if (! $check['ok']) {
            return back()->withErrors(['DB_CONNECTION' => $check['message']])->withInput();
        }

        $env->update([
            'APP_NAME' => $validated['APP_NAME'],
            'APP_URL' => rtrim($validated['APP_URL'], '/'),
            'DB_CONNECTION' => $connection,
            'DB_HOST' => $validated['DB_HOST'] ?? '',
            'DB_PORT' => (string) ($validated['DB_PORT'] ?? ''),
            'DB_DATABASE' => $validated['DB_DATABASE'],
            'DB_USERNAME' => $validated['DB_USERNAME'] ?? '',
            'DB_PASSWORD' => $validated['DB_PASSWORD'] ?? '',
        ]);
        Installer::markStep('environment');

        // Database + admin step lands here next; for now continue to complete placeholder.
        return redirect()->route('installer.database')->with('status', 'Environment saved and database connected.');
    }

    public function database(): View
    {
        return view('installer-wizard::steps.database', [
            'seed' => (bool) config('installer-wizard.run_seed', false),
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
