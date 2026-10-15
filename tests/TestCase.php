<?php

namespace Sbrimon10\InstallerWizard\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use Sbrimon10\InstallerWizard\InstallerWizardServiceProvider;

class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [InstallerWizardServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('installer-wizard.enabled', true);
        $app['config']->set('installer-wizard.route_prefix', 'install');
    }
}
