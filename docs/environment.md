# Environment

Visit `/install/environment` after the requirements check.

The form writes to your app `.env`:

- `APP_NAME`, `APP_URL`
- `DB_CONNECTION` (`mysql`, `pgsql`, `sqlite`; `mariadb` is normalized to `mysql`)
- `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`

Behavior:

1. Validates input (`APP_URL` must be a URL, DB fields required except for `sqlite`).
2. Tests the database over PDO **before** saving. Failures return to the form with the PDO message, `.env` untouched.
3. On success, updates/preserves `.env` formatting (quotes values with spaces) and redirects to the next step (currently `complete` placeholder until the database + admin step lands).

Lock file: `storage/installer.lock` (config `installer-wizard.lock_file`, env `INSTALLER_LOCK_FILE`) is checked first by `CanInstall` on every wizard route.

## Never-stuck design

- While the wizard is open (no lock), it forces **file sessions + file cache** internally, so its pages load even when the saved `.env` database is unreachable. Buyers always get back to the Env step to fix typos instead of a 500.
- Connection tests use Laravel's own connectors (including the SQLite absolute-path rule), so a green test means the app will boot with those values.
- SQLite bare names are normalized to `database/*.sqlite` and the file is created; `.env` keys are de-duplicated on write; `config:clear` runs after every save so the next request boots fresh.
- `php artisan serve` **restarts when `.env` changes** (Laravel dev-server behavior): the save response may drop locally — just refresh, the data is saved and the walk continues. Production php-fpm/nginx is unaffected.
