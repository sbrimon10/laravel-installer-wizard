<?php

namespace Sbrimon10\InstallerWizard\Console;

use Illuminate\Console\Command;
use Sbrimon10\InstallerWizard\Support\Installer;
use Sbrimon10\InstallerWizard\Support\RequirementsChecker;

class WizardCheckCommand extends Command
{
    protected $signature = 'wizard:check';

    protected $description = 'Audit server requirements for the installer wizard (CI-friendly, exit 1 on failure).';

    public function handle(RequirementsChecker $checker): int
    {
        $checks = $checker->check();

        $this->info('PHP '.$checks['php']['current'].' (required >= '.$checks['php']['required'].') '.
            ($checks['php']['ok'] ? 'OK' : 'FAIL'));

        $this->line('Required extensions:');
        foreach ($checks['required'] as $ext => $ok) {
            $this->line(($ok ? '  [OK] ' : '  [XX] ').$ext);
        }

        if (count($checks['optional'])) {
            $this->line('Recommended extensions (warnings only):');
            foreach ($checks['optional'] as $ext => $ok) {
                $this->line(($ok ? '  [OK] ' : '  [..] ').$ext);
            }
        }

        $this->line('Permissions:');
        foreach ($checks['permissions'] as $path => $p) {
            $state = $p['exists'] && $p['writable'] ? 'OK' : 'XX';
            $this->line("  [{$state}] {$path}");
        }

        $this->line('Lock: '.(Installer::installed() ? 'installed ('.Installer::lockPath().')' : 'not installed'));

        if (! $checks['passes']) {
            $this->error('Requirements NOT met.');

            return 1;
        }

        $this->info('All required checks passed.');

        return 0;
    }
}
