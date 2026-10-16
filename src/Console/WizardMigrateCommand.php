<?php

namespace Sbrimon10\InstallerWizard\Console;

use Illuminate\Console\Command;
use Sbrimon10\InstallerWizard\Support\DatabaseManager;

class WizardMigrateCommand extends Command
{
    protected $signature = 'wizard:migrate {--fresh : Drop all tables first}';

    protected $description = 'Run installer migrations (+ configured seeder) from CLI — the timeout-safe path for big seeds and shared hosting.';

    public function handle(DatabaseManager $db): int
    {
        $result = $db->migrate(fresh: (bool) $this->option('fresh'));

        if ($result['output'] !== '') {
            $this->line($result['output']);
        }

        if (! $result['ok']) {
            $this->error($result['message']);

            return 1;
        }

        $this->info($result['message']);

        return 0;
    }
}
