# installer-wizard skill

Use when working with `sbrimon10/laravel-installer-wizard`.

- Config key: `installer-wizard`. Views namespace: `installer-wizard::`.
- Routes: `/install`, `/install/requirements`, `/install/complete` (prefix configurable).
- Middleware `CanInstall` blocks wizard if `storage/installer.lock` exists.
- Docs source of truth: package `docs/*.md`. Never author docs in website admin.
