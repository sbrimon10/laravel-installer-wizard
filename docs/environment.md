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
