# Database + Admin

Flow after `/install/environment`:

## 1. `/install/database` (GET + POST)

- GET shows a **Run migrations** form with an optional **Fresh install** checkbox (`migrate:fresh`).
- POST calls `DatabaseManager::migrate($fresh)`:
  - runs host-app `migrate` (or `migrate:fresh`) with `--force`
  - seeding follows config: custom `seed_class` (`INSTALLER_SEED_CLASS`, e.g. `Database\Seeders\DatabaseSeeder`) wins; otherwise `--seed` when `run_seed` (`INSTALLER_RUN_SEED`) is true
  - lifts the PHP time limit (`set_time_limit(0)`) so big seeds survive
  - captures `Artisan::output()` and shows it on success/failure
- Success redirects to `/install/admin`. Failures return with the error + output, `.env` untouched.
- **Timeouts:** browsers/shared hosts can still kill very long requests. For huge seeds, run `php artisan wizard:migrate --fresh` over SSH instead — same code, no HTTP timeout.

## Custom seeds: yes, let them

Set `seed_class` in published config (or `INSTALLER_SEED_CLASS`) and the buyer never touches code:

```php
'seed_class' => 'Database\Seeders\DatabaseSeeder',
```

Keep seeders idempotent (`firstOrCreate`) so re-running the wizard never duplicates demo data.

## 2. `/install/admin` (GET + POST)

- GET shows name / email / password + confirmation.
- POST validates (`password|min:8|confirmed`) and calls `AdminManager::create()`:
  - model from `installer-wizard.admin_model` (`INSTALLER_ADMIN_MODEL`), default host `auth.providers.users.model`
  - requires the `users` table (run migrations first)
  - existing email → name + password reset; new email → created with `email_verified_at`
  - passwords hashed via `Hash::make` (safe with `hashed` casts)
- Success redirects to `/install/complete`.

## 3. `/install/complete` (GET + POST)

- GET shows **Finish installation** when no lock exists.
- POST runs `post_commands` (`config:clear`, `route:clear`, `view:clear` by default — so the fresh `.env` takes effect) plus `storage:link` (disable via `INSTALLER_STORAGE_LINK=false`), then writes the lock via `Installer::markInstalled()`.
- Post-command failures are reported, never fatal — the lock is still written with a warning listing what to run manually.
- Afterwards `CanInstall` 404s every wizard route except `complete`, which shows the locked state.
- Production: set `INSTALLER_ENABLED=false` to unregister routes entirely.

## Step order is enforced

Every step (except welcome) carries an `installer.ensure-order:{step}` middleware:
each controller marks its step (`Installer::markStep`) only after its data is
submitted successfully, and jumping ahead redirects to the first incomplete
step with an explanatory message. Typing `/install/admin` on a fresh session
lands you on welcome — never on admin.
