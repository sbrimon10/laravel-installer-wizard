<?php

namespace Sbrimon10\InstallerWizard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sbrimon10\InstallerWizard\Support\Installer;
use Symfony\Component\HttpFoundation\Response;

/**
 * Opt-in middleware for the HOST app's web group. Sends visitors to
 * the installer until the lock file exists. The package never forces
 * this on — the host registers it in bootstrap/app.php.
 */
class RedirectToInstaller
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($this->shouldRedirect($request)) {
            return redirect()->route('installer.welcome');
        }

        return $next($request);
    }

    protected function shouldRedirect(Request $request): bool
    {
        if (! config('installer-wizard.enabled', true)) {
            return false;
        }

        if (! config('installer-wizard.auto_redirect', true)) {
            return false;
        }

        if (Installer::installed()) {
            return false;
        }

        if (! $request->isMethod('get') || $request->expectsJson()) {
            return false;
        }

        $prefix = trim((string) config('installer-wizard.route_prefix', 'install'), '/');

        // Never intercept the wizard itself.
        if ($request->is($prefix) || $request->is($prefix.'/*')) {
            return false;
        }

        $path = $request->getPathInfo();

        foreach ((array) config('installer-wizard.redirect_paths', ['/']) as $watched) {
            $watched = '/'.ltrim((string) $watched, '/');

            if ($path === $watched) {
                return true;
            }
        }

        return false;
    }
}
