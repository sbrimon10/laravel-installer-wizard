<?php

use Sbrimon10\InstallerWizard\Support\DatabaseManager;
use Sbrimon10\InstallerWizard\Tests\TestCase;

it('loads welcome page', function () {
    $response = $this->get('/install');

    $response->assertOk();
    $response->assertSee('Laravel Installer Wizard');
});

it('loads environment page', function () {
    $response = $this->get('/install/environment');

    $response->assertOk();
    $response->assertSee('Environment', false);
});

it('loads database, admin and complete pages', function () {
    $this->get('/install/requirements')->assertOk();
    $this->get('/install/database')->assertOk();
    $this->get('/install/admin')->assertOk();
    $this->get('/install/complete')->assertOk();
});

it('rejects bad database connection without touching .env', function () {
    $result = app(DatabaseManager::class)->testConnection([
        'connection' => 'mysql',
        'host' => 'invalid-host-that-does-not-exist',
        'port' => '3306',
        'database' => 'nope',
        'username' => 'nope',
        'password' => 'nope',
    ]);

    expect($result['ok'])->toBeFalse();
});
