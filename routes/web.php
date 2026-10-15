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
    Route::get('/complete', [InstallerController::class, 'complete'])->name('complete');
});
