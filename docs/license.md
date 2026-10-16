# License

Disabled by default. Enable only if you sell (`INSTALLER_LICENSE_ENABLED=true`).

When enabled, a **License** step appears between Requirements and Env.
The code is verified, stored in session, and written to `.env` under
`license_env_key` (default `APP_PURCHASE_CODE`).

## Verifiers

| Class | Use |
|---|---|
| `EnvatoVerifier` (default) | CodeCanyon sales. Format-checks always; calls the Envato API when `INSTALLER_ENVATO_TOKEN` is set, otherwise format-only mode. |
| `NullVerifier` | Local dev bypass, accepts any non-empty code. Never ship this. |

Select via `INSTALLER_LICENSE_VERIFIER`. Bring your own by implementing
`Sbrimon10\InstallerWizard\Support\LicenseVerifier::verify(string $code): array{ok, message}`
— e.g. a call to your own website `/api/verify-license`.
