<?php

namespace Sbrimon10\InstallerWizard\Console;

use Illuminate\Console\Command;
use Sbrimon10\InstallerWizard\Support\Installer;

class WizardResetCommand extends Command
{
    protected $signature = 'wizard:reset {--force : Skip confirmation}';

    protected $description = 'Delete the installer lock file so the wizard can run again (dev/repair).';

    public function handle(): int
    {
        if (! Installer::installed()) {
            $this->info('Nothing to reset — no lock file at '.Installer::lockPath().'.');

            return 0;
        }

        if (! $this->option('force') && ! $this->confirm('Delete '.Installer::lockPath().' and re-open the wizard?')) {
            return 0;
        }

        @unlink(Installer::lockPath());

        $this->info('Lock removed. The wizard is open again.');

        return 0;
    }
}
