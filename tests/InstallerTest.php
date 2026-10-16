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

it('probes connections over JSON without writing .env', function () {
    $this->withSession(['installer.passed' => ['welcome' => true, 'requirements' => true]])
        ->postJson('/install/api/test-connection', [
            'APP_NAME' => 'Test',
            'APP_URL' => 'http://localhost',
            'DB_CONNECTION' => 'sqlite',
            'DB_DATABASE' => ':memory:',
        ])
        ->assertOk()
        ->assertJson(['ok' => true]);
});

it('reports installer status as JSON', function () {
    $this->getJson('/install/api/status')
        ->assertOk()
        ->assertJson(['installed' => false]);
});

it('groups appended env keys and drops duplicates', function () {
    $file = tempnam(sys_get_temp_dir(), 'env');
    file_put_contents($file, "APP_NAME=x\nDB_HOST=old\nDB_HOST=dup\n");

    $mgr = new class($file) extends \Sbrimon10\InstallerWizard\Support\EnvManager
    {
        public function __construct(private string $f) {}
        public function path(): string
        {
            return $this->f;
        }
    };

    $mgr->update(['DB_HOST' => 'new', 'DB_DATABASE' => 'app']);

    $content = file_get_contents($file);
    unlink($file);

    expect(substr_count($content, 'DB_HOST='))->toBe(1)
        ->and($content)->toContain('DB_DATABASE=app')
        ->and(strpos($content, 'DB_DATABASE'))->toBeGreaterThan(strpos($content, 'DB_HOST'));
});

it('skips env write when nothing changed', function () {
    $file = tempnam(sys_get_temp_dir(), 'env');
    file_put_contents($file, "A=1\nB=\"x y\"\n");

    $mgr = new class($file) extends \Sbrimon10\InstallerWizard\Support\EnvManager
    {
        public function __construct(private string $f) {}
        public function path(): string
        {
            return $this->f;
        }
    };

    expect($mgr->update(['A' => '1', 'B' => 'x y']))->toBeFalse()
        ->and(file_get_contents($file))->toBe("A=1\nB=\"x y\"\n");

    expect($mgr->update(['A' => '2']))->toBeTrue();

    unlink($file);
});
