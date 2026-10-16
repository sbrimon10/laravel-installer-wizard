<?php

namespace Sbrimon10\InstallerWizard\Support\License;

use Illuminate\Support\Facades\Http;
use Sbrimon10\InstallerWizard\Support\LicenseVerifier;

/**
 * CodeCanyon / Envato verifier. Always checks code format first.
 * With INSTALLER_ENVATO_TOKEN set it calls the real Envato API;
 * without a token it accepts any well-formed code (format-only mode
 * for sellers who verify manually or via their own server).
 */
class EnvatoVerifier implements LicenseVerifier
{
    public function verify(string $code): array
    {
        $code = trim($code);

        if (! preg_match('/^[a-z0-9]{8}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{4}-[a-z0-9]{12}$/i', $code)) {
            return ['ok' => false, 'message' => 'Code format looks wrong. Envato codes look like xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx.'];
        }

        $token = config('installer-wizard.license_envato_token');

        if (blank($token)) {
            return ['ok' => true, 'message' => 'Format OK (API check skipped — no Envato token configured).'];
        }

        try {
            $response = Http::withToken($token)
                ->timeout(10)
                ->get('https://api.envato.com/v3/market/author/sale', ['code' => $code]);

            if ($response->successful()) {
                return ['ok' => true, 'message' => 'License verified with Envato.'];
            }

            return ['ok' => false, 'message' => 'Envato rejected this code (HTTP '.$response->status().').'];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'Could not reach Envato: '.$e->getMessage()];
        }
    }
}
