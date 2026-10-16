<?php

namespace Sbrimon10\InstallerWizard\Support\License;

use Sbrimon10\InstallerWizard\Support\LicenseVerifier;

/**
 * Dev bypass: accepts any non-empty code. Never use in production.
 * Select via INSTALLER_LICENSE_VERIFIER.
 */
class NullVerifier implements LicenseVerifier
{
    public function verify(string $code): array
    {
        if (trim($code) === '') {
            return ['ok' => false, 'message' => 'Enter a license code.'];
        }

        return ['ok' => true, 'message' => 'Accepted (dev bypass).'];
    }
}
