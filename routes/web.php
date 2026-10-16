<?php

use Illuminate\Support\Facades\Route;
use Sbrimon10\InstallerWizard\Http\Controllers\InstallerController;
use Sbrimon10\InstallerWizard\Http\Middleware\CanInstall;

$prefix = config('installer-wizard.route_prefix', 'install');

// CanInstall runs FIRST so the lock file is checked before session/CSRF/controller logic.
$middleware = array_merge(
    [CanInstall::class],
    config('installer-wizard.middleware', ['web'])
);

Route::middleware($middleware)->prefix($prefix)->name('installer.')->group(function () {
    Route::get('/', [InstallerController::class, 'welcome'])->name('welcome');
    Route::get('/requirements', [InstallerController::class, 'requirements'])->name('requirements');
    Route::get('/license', [InstallerController::class, 'license'])->name('license');
    Route::post('/license', [InstallerController::class, 'verifyLicense'])->name('license.verify');
    Route::get('/environment', [InstallerController::class, 'environment'])->name('environment');
    Route::post('/environment', [InstallerController::class, 'saveEnvironment'])->name('environment.save');
    Route::get('/database', [InstallerController::class, 'database'])->name('database');
    Route::post('/database', [InstallerController::class, 'runMigrations'])->name('database.run');
    Route::get('/admin', [InstallerController::class, 'admin'])->name('admin');
    Route::post('/admin', [InstallerController::class, 'storeAdmin'])->name('admin.store');
    Route::get('/complete', [InstallerController::class, 'complete'])->name('complete');
    Route::post('/complete', [InstallerController::class, 'finish'])->name('finish');
});
