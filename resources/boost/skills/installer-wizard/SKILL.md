# installer-wizard skill

Use when working with `sbrimon10/laravel-installer-wizard`.

- Config key: `installer-wizard`. Views namespace: `installer-wizard::`.
- Routes: `/install`, `/install/requirements` (required blocks, recommended warns), optional `/install/license` (GET+POST, Envato/Null/custom verifier, code saved to `.env`), `/install/environment` (GET+POST), `/install/database` (GET+POST), `/install/admin` (GET+POST), `/install/complete` (GET+POST finish) (prefix configurable).
- Middleware `CanInstall` runs first in group; blocks wizard if `Installer::installed()` (storage lock) exists.
- Opt-in `RedirectToInstaller` for host web group sends `redirect_paths` (default `/`) to wizard until installed.
- Env: `EnvManager::get/update` preserves `.env` formatting, de-duplicates keys (SQLite hides host/port/user/pass via JS, bare names normalized to absolute + touched); `DatabaseManager::testConnection` uses framework connectors on a purged probe + `migrate($fresh)` via Artisan (seed_class wins, else run_seed; time limit lifted); `AdminManager::create` uses `admin_model` config, requires users table. Wizard forces file session/cache while unlocked so broken DB never 500s it.
- Order enforced: `EnsureStepOrder` (`installer.ensure-order:{step}`) + `Installer::markStep/passed/firstIncomplete`; finish runs `post_commands` + `storage:link` then locks.
- Commands: `wizard:check` (audit, exit 1 on fail), `wizard:migrate [--fresh]` (timeout-safe CLI path), `wizard:reset [--force]` (dev only).
- Branding via `title`/`description` config; credit line stays. Docs source of truth: package `docs/*.md`. Never author docs in website admin.
