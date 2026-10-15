<?php

namespace Sbrimon10\InstallerWizard\Support;

interface LicenseVerifier
{
    public function verify(string $code): bool;
}
