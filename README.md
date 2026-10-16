# laravel-installer-wizard

Drop-in Laravel installer wizard — `sbrimon10/laravel-installer-wizard`.

## Install (dev, path repo)

```json
"repositories": [{ "type": "path", "url": "C:/xampp/htdocs/sb-packages/laravel-installer-wizard", "options": {"symlink": true} }]
```

```bash
composer require sbrimon10/laravel-installer-wizard:@dev
php artisan serve
# visit http://127.0.0.1:8000/install
```

## Publish

```bash
php artisan vendor:publish --tag=installer-wizard-config
php artisan vendor:publish --tag=installer-wizard-views
```

Disable after install: `INSTALLER_ENABLED=false`.

## Wizard

1. `/install` welcome → 2. `/install/requirements` (required blocks, recommended warns) → [3. `/install/license` if enabled] → Env (writes `.env`, PDO-tests DB first) → Database (`migrate --force`, optional fresh + seed) → Admin (create/reset) → Complete (writes lock).

Brand it from config: `INSTALLER_TITLE`, `INSTALLER_DESCRIPTION`. Docs in `docs/`.

## Screenshots

Captured from the preview app at release time (see `docs/screenshots/`):

- `01-welcome.png` — welcome + stepper
- `02-requirements.png` — required / recommended / server info
- `03-environment.png` — `.env` form
- `04-database.png` — migrate runner
- `05-admin.png` — admin creation

## FAQ

- **Shared hosting without SSH?** Everything above runs in the browser; `wizard:check` is optional. Fix red items (PHP version, extensions need host support; `storage` + `bootstrap/cache` need 775).
- **SQLite?** Pick SQLite in the Env step and point `DB_DATABASE` at `database/database.sqlite`.
- **Re-open the wizard?** `php artisan wizard:reset` (dev only), or delete `storage/installer.lock`.
