<?php

namespace Sbrimon10\InstallerWizard\Support;

use PDO;
use Throwable;

class DatabaseManager
{
    /**
     * Attempt a PDO connection with the given settings. No side effects.
     *
     * @param  array{connection?: string, host?: ?string, port?: mixed, database?: ?string, username?: ?string, password?: ?string}  $config
     * @return array{ok: bool, message: string}
     */
    public function testConnection(array $config): array
    {
        $driver = strtolower((string) ($config['connection'] ?? 'mysql'));
        $driver = $driver === 'mariadb' ? 'mysql' : $driver;

        try {
            if ($driver === 'sqlite') {
                $database = (string) ($config['database'] ?? ':memory:');
                $pdo = new PDO("sqlite:{$database}", null, null, [
                    PDO::ATTR_TIMEOUT => 5,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
            } elseif ($driver === 'pgsql') {
                $dsn = sprintf(
                    'pgsql:host=%s;port=%s;dbname=%s',
                    $config['host'] ?? '127.0.0.1',
                    $config['port'] ?? '5432',
                    $config['database'] ?? ''
                );
                $pdo = new PDO($dsn, (string) ($config['username'] ?? ''), (string) ($config['password'] ?? ''), [
                    PDO::ATTR_TIMEOUT => 5,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
            } else {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    $config['host'] ?? '127.0.0.1',
                    $config['port'] ?? '3306',
                    $config['database'] ?? ''
                );
                $pdo = new PDO($dsn, (string) ($config['username'] ?? ''), (string) ($config['password'] ?? ''), [
                    PDO::ATTR_TIMEOUT => 5,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]);
            }

            $pdo->query('SELECT 1');

            return ['ok' => true, 'message' => 'Connection successful.'];
        } catch (Throwable $e) {
            return ['ok' => false, 'message' => 'Connection failed: '.$e->getMessage()];
        }
    }

    // TODO: run migrations + seed (database step).
    public function stub(): bool
    {
        return true;
    }
}
