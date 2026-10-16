<?php

namespace Sbrimon10\InstallerWizard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sbrimon10\InstallerWizard\Support\Installer;
use Symfony\Component\HttpFoundation\Response;

class CanInstall
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Lock file FIRST — before any other check.
        if (Installer::installed() && ! $request->routeIs('installer.complete')) {
            abort(404, 'Application already installed.');
        }

        // 2. Then master switch.
        if (! config('installer-wizard.enabled', true)) {
            abort(404);
        }

        // 3. Decouple the wizard from the host's (possibly broken, just-written)
        // .env: cookie sessions + file cache keep every wizard page loadable
        // even when the configured database is unreachable, so buyers can
        // always go back to the Env step and fix it instead of hitting a 500.
        // Cookie sessions also survive dev-server restarts: a response killed
        // mid-flight can't corrupt server-side session files. The cookie name
        // is pinned (never derived from APP_NAME) so renaming the app
        // mid-install doesn't orphan the walk.
        if (! Installer::installed()) {
            config([
                'session.driver' => 'cookie',
                'session.cookie' => 'installer_session',
                'cache.default' => 'file',
                'queue.default' => 'sync',
            ]);
        }

        return $next($request);
    }
}
