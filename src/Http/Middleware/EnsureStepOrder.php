<?php

namespace Sbrimon10\InstallerWizard\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Sbrimon10\InstallerWizard\Support\Installer;
use Symfony\Component\HttpFoundation\Response;

/**
 * Blocks jumping ahead: a step only opens when every previous step
 * left its completion flag in session (i.e. its data was submitted).
 * Usage: ->middleware('installer.ensure-order:environment')
 */
class EnsureStepOrder
{
    public function handle(Request $request, Closure $next, string $step): Response
    {
        // Locked = install finished; let CanInstall/complete handle it.
        if (Installer::installed()) {
            return $next($request);
        }

        // Step not in current order (e.g. license while disabled):
        // let the controller decide (it redirects to the next live step).
        if (! in_array($step, Installer::order(), true)) {
            return $next($request);
        }

        $missing = Installer::firstIncomplete($step);

        if ($missing !== null) {
            return redirect()
                ->route(Installer::routeFor($missing))
                ->with('error', 'Please complete the '.ucfirst($missing).' step first.');
        }

        return $next($request);
    }
}
