# installer-wizard skill

Use when working with `sbrimon10/laravel-installer-wizard`.

- Config key: `installer-wizard`. Views namespace: `installer-wizard::`.
- Routes: `/install`, `/install/requirements`, `/install/environment` (GET+POST), `/install/database` (GET+POST), `/install/admin` (GET+POST), `/install/complete` (GET+POST finish) (prefix configurable).
- Middleware `CanInstall` runs first in group; blocks wizard if `Installer::installed()` (storage lock) exists.
- Opt-in `RedirectToInstaller` for host web group sends `redirect_paths` (default `/`) to wizard until installed; never forced by package.
- Env: `EnvManager::get/update` preserves `.env` formatting; `DatabaseManager::testConnection` PDO-checks before save + `migrate($fresh,$seed)` via Artisan; `AdminManager::create` uses `admin_model` config, requires users table.
- Docs source of truth: package `docs/*.md`. Never author docs in website admin.
