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

1. `/install` welcome → 2. `/install/requirements` → 3. `/install/environment` (writes `.env`, PDO-tests DB first) → 4. DB + admin (next) → 5. `/install/complete`.

See `docs/` + `CHANGELOG.md`. Full docs site: see `../WEBSITE_PLAN.md`.
