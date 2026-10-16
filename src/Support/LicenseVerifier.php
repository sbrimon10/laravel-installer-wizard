<?php

namespace Sbrimon10\InstallerWizard\Support;

interface LicenseVerifier
{
    /**
     * @return array{ok: bool, message: string}
     */
    public function verify(string $code): array;
}
