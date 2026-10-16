<?php

use Illuminate\Support\Facades\Route;
use Sbrimon10\InstallerWizard\Http\Controllers\InstallerController;
use Sbrimon10\InstallerWizard\Http\Middleware\CanInstall;
use Sbrimon10\InstallerWizard\Http\Middleware\EnsureStepOrder;

$prefix = config('installer-wizard.route_prefix', 'install');

// CanInstall runs FIRST so the lock file is checked before session/CSRF/controller logic.
$middleware = array_merge(
    [CanInstall::class],
    config('installer-wizard.middleware', ['web'])
);

Route::middleware($middleware)->prefix($prefix)->name('installer.')->group(function () {
    Route::get('/', [InstallerController::class, 'welcome'])->name('welcome');

    Route::get('/requirements', [InstallerController::class, 'requirements'])
        ->middleware('installer.ensure-order:requirements')->name('requirements');

    Route::get('/license', [InstallerController::class, 'license'])
        ->middleware('installer.ensure-order:license')->name('license');
    Route::post('/license', [InstallerController::class, 'verifyLicense'])
        ->middleware('installer.ensure-order:license')->name('license.verify');

    Route::get('/environment', [InstallerController::class, 'environment'])
        ->middleware('installer.ensure-order:environment')->name('environment');
    Route::post('/environment', [InstallerController::class, 'saveEnvironment'])
        ->middleware('installer.ensure-order:environment')->name('environment.save');

    Route::get('/database', [InstallerController::class, 'database'])
        ->middleware('installer.ensure-order:database')->name('database');
    Route::post('/database', [InstallerController::class, 'runMigrations'])
        ->middleware('installer.ensure-order:database')->name('database.run');

    Route::get('/admin', [InstallerController::class, 'admin'])
        ->middleware('installer.ensure-order:admin')->name('admin');
    Route::post('/admin', [InstallerController::class, 'storeAdmin'])
        ->middleware('installer.ensure-order:admin')->name('admin.store');

    Route::get('/complete', [InstallerController::class, 'complete'])
        ->middleware('installer.ensure-order:complete')->name('complete');
    Route::post('/complete', [InstallerController::class, 'finish'])
        ->middleware('installer.ensure-order:complete')->name('finish');

    // JSON API (vanilla-JS frontend, no page reloads).
    Route::get('/api/status', [InstallerController::class, 'status'])->name('api.status');
    Route::post('/api/test-connection', [InstallerController::class, 'testConnection'])
        ->middleware('installer.ensure-order:environment')->name('api.test-connection');
});
