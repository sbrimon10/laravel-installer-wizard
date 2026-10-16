<?php

namespace Sbrimon10\InstallerWizard;

use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Sbrimon10\InstallerWizard\Console\WizardCheckCommand;
use Sbrimon10\InstallerWizard\Console\WizardMigrateCommand;
use Sbrimon10\InstallerWizard\Console\WizardResetCommand;
use Sbrimon10\InstallerWizard\Http\Middleware\CanInstall;
use Sbrimon10\InstallerWizard\Http\Middleware\EnsureStepOrder;
use Sbrimon10\InstallerWizard\Http\Middleware\RedirectToInstaller;

class InstallerWizardServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/installer-wizard.php', 'installer-wizard');

        // Register early so host apps can reference it even if our routes load first.
        /** @var Router $router */
        $router = $this->app['router'];
        $router->aliasMiddleware('installer.can-install', CanInstall::class);
        $router->aliasMiddleware('installer.redirect', RedirectToInstaller::class);
        $router->aliasMiddleware('installer.ensure-order', EnsureStepOrder::class);
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/installer-wizard.php' => config_path('installer-wizard.php'),
        ], 'installer-wizard-config');

        $this->publishes([
            __DIR__.'/../resources/views' => resource_path('views/vendor/installer-wizard'),
        ], 'installer-wizard-views');

        $this->loadViewsFrom(__DIR__.'/../resources/views', 'installer-wizard');

        if ($this->app->runningInConsole()) {
            $this->commands([WizardCheckCommand::class, WizardMigrateCommand::class, WizardResetCommand::class]);
        }

        // Load routes first-party; CanInstall inside the group still checks
        // the storage lock file before anything else reaches a controller.
        if (! config('installer-wizard.enabled', true)) {
            return;
        }

        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
    }
}
