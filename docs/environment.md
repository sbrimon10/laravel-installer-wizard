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

- While the wizard is open (no lock), it forces **cookie sessions + file cache** internally, so its pages load even when the saved `.env` database is unreachable, and a dev-server restart mid-request can't corrupt session state. Buyers always get back to the Env step to fix typos instead of a 500.
- Connection tests use Laravel's own connectors (including the SQLite absolute-path rule), so a green test means the app will boot with those values.
- SQLite bare names are normalized to `database/*.sqlite` and the file is created; `.env` keys are de-duplicated on write; `config:clear` runs after every save so the next request boots fresh.
- `php artisan serve` **restarts when `.env` changes** (Laravel dev-server behavior): the save response may drop locally — the page then polls `status`, re-submits automatically (writes are skipped when values are identical, so the retry can't restart the server again) and continues. For local testing prefer `php artisan serve --no-reload`. Production php-fpm/nginx is unaffected.

## Live test without reload (vanilla JS, zero dependencies)

The Env page has a **Test connection** button that POSTs JSON to
`/install/api/test-connection` — validated and probed, but nothing is
written, so no page reload and no server restart. Results show inline plus a
toast. **Save & continue** submits via fetch too: if the dev server restarts
on the `.env` write and drops the response, the page polls
`GET /install/api/status` until the server is back, then follows the
`redirect`. Browsers without JS fall back to plain form POSTs.

API summary: `POST /install/api/test-connection` → `200 {ok,message}` or
`422`; `GET /install/api/status` → `{installed, passed[], next}`.
