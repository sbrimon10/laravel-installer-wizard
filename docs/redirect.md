# Auto-redirect to the installer

The package only serves `/install/*`. It never hijacks your homepage on its own.
To send visitors from `/` to `/install` until the app is installed, register the
opt-in middleware in the **host app** `bootstrap/app.php`:

```php
use Sbrimon10\InstallerWizard\Http\Middleware\RedirectToInstaller;

->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        RedirectToInstaller::class,
    ]);
})
```

Behavior:

- GET requests to `redirect_paths` (default `['/']`) redirect to `installer.welcome` while no lock file exists.
- Skipped when: package disabled, `auto_redirect` false, already installed, non-GET, JSON requests, or the request is already inside the install prefix.
- After `storage/installer.lock` exists it does nothing — remove it from the stack or leave it, both are safe.

Config:

```php
'auto_redirect' => env('INSTALLER_AUTO_REDIRECT', true),
'redirect_paths' => ['/'], // add '/login', '/register' if you sell a closed app
```
