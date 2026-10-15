# installer-wizard skill

Use when working with `sbrimon10/laravel-installer-wizard`.

- Config key: `installer-wizard`. Views namespace: `installer-wizard::`.
- Routes: `/install`, `/install/requirements`, `/install/environment` (GET+POST), `/install/complete` (prefix configurable).
- Middleware `CanInstall` runs first in group; blocks wizard if `Installer::installed()` (storage lock) exists.
- Env: `EnvManager::get/update` preserves `.env` formatting; `DatabaseManager::testConnection` PDO-checks before save; controller validates then redirects to next step.
- Docs source of truth: package `docs/*.md`. Never author docs in website admin.
