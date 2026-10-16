<?php

use Sbrimon10\InstallerWizard\Support\DatabaseManager;
use Sbrimon10\InstallerWizard\Tests\TestCase;

it('loads welcome page', function () {
    $response = $this->get('/install');

    $response->assertOk();
    $response->assertSee('Laravel Installer Wizard');
});

it('blocks jumping ahead without previous steps', function () {
    $this->get('/install/environment')->assertRedirect(route('installer.welcome'));
    $this->get('/install/database')->assertRedirect(route('installer.welcome'));
    $this->get('/install/admin')->assertRedirect(route('installer.welcome'));
    $this->get('/install/complete')->assertRedirect(route('installer.welcome'));
});

it('opens each step once previous steps passed', function () {
    $passed = ['welcome' => true, 'requirements' => true];

    $this->withSession(['installer.passed' => $passed])
        ->get('/install/environment')->assertOk();

    $passed['environment'] = true;

    $this->withSession(['installer.passed' => $passed])
        ->get('/install/database')->assertOk();

    $passed['database'] = true;

    $this->withSession(['installer.passed' => $passed])
        ->get('/install/admin')->assertOk();

    $passed['admin'] = true;

    $this->withSession(['installer.passed' => $passed])
        ->get('/install/complete')->assertOk();
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

it('accepts sqlite memory through the framework connector', function () {
    $result = app(DatabaseManager::class)->testConnection([
        'connection' => 'sqlite',
        'database' => ':memory:',
    ]);

    expect($result['ok'])->toBeTrue();
});

it('skips license step when disabled', function () {
    $this->get('/install/license')->assertRedirect('/install/environment');
});
