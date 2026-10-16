# Database + Admin

Flow after `/install/environment`:

## 1. `/install/database` (GET + POST)

- GET shows a **Run migrations** form with an optional **Fresh install** checkbox (`migrate:fresh`).
- POST calls `DatabaseManager::migrate($fresh, $seed)`:
  - runs host-app `migrate` (or `migrate:fresh`) with `--force`
  - adds `--seed` when `installer-wizard.run_seed` (`INSTALLER_RUN_SEED`) is true
  - captures `Artisan::output()` and shows it on success/failure
- Success redirects to `/install/admin`. Failures return with the error + output, `.env` untouched.

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
- POST writes the lock via `Installer::markInstalled()` (`storage/installer.lock` by default).
- Afterwards `CanInstall` 404s every wizard route except `complete`, which shows the locked state.
- Production: set `INSTALLER_ENABLED=false` to unregister routes entirely.
