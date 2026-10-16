# Artisan commands (`wizard:` namespace)

We use `wizard:` — short, matches the package name, and avoids collisions
with other installer packages that claim `installer:`.

## `php artisan wizard:check`

Audits PHP, required/optional extensions and permissions without touching anything.
Exit `0` when install can proceed, `1` otherwise — safe for deploy scripts and CI:

```bash
php artisan wizard:check || echo "server not ready"
```

## `php artisan wizard:reset [--force]`

Deletes the lock file so the wizard opens again. For dev machines and
repair jobs only — never expose it to buyers.

## `php artisan wizard:migrate [--fresh]`

Runs the same migration (+ configured seeder) as the Database step, but from
CLI. This is the timeout-safe path: browsers and shared hosting often kill
long HTTP requests, while CLI has no request timeout. The web step also lifts
its own PHP time limit via `set_time_limit(0)`, but for huge seeds prefer:

```bash
php artisan wizard:migrate --fresh
```
