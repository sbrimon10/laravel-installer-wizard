<?php

namespace Sbrimon10\InstallerWizard\Support;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

class DatabaseManager
{
    /**
     * Attempt a connection with the given settings using Laravel's own
     * connectors (same semantics the app will boot with — including the
     * SQLite absolute-path rule). No side effects: tested on a throwaway
     * connection name that is purged afterwards.
     *
     * @param  array{connection?: string, host?: ?string, port?: mixed, database?: ?string, username?: ?string, password?: ?string}  $config
     * @return array{ok: bool, message: string}
     */
    public function testConnection(array $config): array
    {
        $driver = strtolower((string) ($config['connection'] ?? 'mysql'));
        $driver = $driver === 'mariadb' ? 'mysql' : $driver;

        $name = 'installer_probe';

        try {
            config(['database.connections.'.$name => match ($driver) {
                'sqlite' => [
                    'driver' => 'sqlite',
                    'database' => (string) ($config['database'] ?? ':memory:'),
                    'prefix' => '',
                    'foreign_key_constraints' => true,
                ],
                'pgsql' => [
                    'driver' => 'pgsql',
                    'host' => $config['host'] ?? '127.0.0.1',
                    'port' => $config['port'] ?? '5432',
                    'database' => $config['database'] ?? '',
                    'username' => $config['username'] ?? '',
                    'password' => $config['password'] ?? '',
                    'charset' => 'utf8',
                    'prefix' => '',
                    'prefix_indexes' => true,
                    'search_path' => 'public',
                    'sslmode' => 'prefer',
                ],
                default => [
                    'driver' => 'mysql',
                    'host' => $config['host'] ?? '127.0.0.1',
                    'port' => $config['port'] ?? '3306',
                    'database' => $config['database'] ?? '',
                    'username' => $config['username'] ?? '',
                    'password' => $config['password'] ?? '',
                    'charset' => 'utf8mb4',
                    'collation' => 'utf8mb4_unicode_ci',
                    'prefix' => '',
                    'strict' => true,
                    'engine' => null,
                ],
            }]);

            DB::purge($name);
            DB::connection($name)->getPdo()->query('SELECT 1');

            return ['ok' => true, 'message' => 'Connection successful.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Connection failed: '.$e->getMessage()];
        } finally {
            try {
                DB::purge($name);
            } catch (Throwable) {
                // Never let cleanup break the result.
            }
        }
    }

    /**
     * Run host-app migrations. Seeding follows config: a custom
     * seed_class wins, otherwise --seed when run_seed is true.
     * Lifts the PHP time limit so big seeds survive shared hosting.
     *
     * @return array{ok: bool, message: string, output: string}
     */
    public function migrate(bool $fresh = false): array
    {
        if (function_exists('set_time_limit')) {
            set_time_limit(0);
        }

        try {
            $command = $fresh ? 'migrate:fresh' : 'migrate';
            $params = ['--force' => true];
            $seedClass = config('installer-wizard.seed_class');

            if (! $seedClass && config('installer-wizard.run_seed')) {
                $params['--seed'] = true;
            }

            $exit = Artisan::call($command, $params);
            $output = Artisan::output();

            if ($exit !== 0) {
                return ['ok' => false, 'message' => 'Migration failed (exit '.$exit.').', 'output' => $output];
            }

            if ($seedClass) {
                $seedExit = Artisan::call('db:seed', ['--class' => $seedClass, '--force' => true]);
                $output .= "\n".Artisan::output();

                if ($seedExit !== 0) {
                    return ['ok' => false, 'message' => "Seeder [{$seedClass}] failed (exit {$seedExit}).", 'output' => $output];
                }
            }

            return ['ok' => true, 'message' => 'Migrations completed.', 'output' => $output];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Migration failed: '.$e->getMessage(), 'output' => ''];
        }
    }
}