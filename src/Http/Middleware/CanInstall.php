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

        return $next($request);
    }
}
