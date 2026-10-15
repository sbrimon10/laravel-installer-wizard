<?php

use Sbrimon10\InstallerWizard\Tests\TestCase;

it('loads welcome page', function () {
    $response = $this->get('/install');

    $response->assertOk();
    $response->assertSee('Laravel Installer Wizard');
});
