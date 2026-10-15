<?php

namespace Sbrimon10\InstallerWizard\Http\Controllers;

use Illuminate\Contracts\View\View;
use Sbrimon10\InstallerWizard\Support\Installer;
use Sbrimon10\InstallerWizard\Support\RequirementsChecker;

class InstallerController
{
    public function welcome(): View
    {
        return view('installer-wizard::steps.welcome');
    }

    public function requirements(RequirementsChecker $checker): View
    {
        return view('installer-wizard::steps.requirements', [
            'checks' => $checker->check(),
        ]);
    }

    public function complete(): View
    {
        $installed = Installer::installed();

        return view('installer-wizard::steps.complete', compact('installed'));
    }
}
