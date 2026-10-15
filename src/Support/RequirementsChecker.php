<?php

namespace Sbrimon10\InstallerWizard\Support;

class RequirementsChecker
{
    /**
     * @return array{php: array, extensions: array, permissions: array, passes: bool}
     */
    public function check(): array
    {
        $required = config('installer-wizard.requirements', []);

        $phpRequired = $required['php'] ?? '8.3.0';
        $phpOk = version_compare(PHP_VERSION, $phpRequired, '>=');

        $extensions = [];
        foreach ($required['extensions'] ?? [] as $ext) {
            $loaded = extension_loaded($ext);
            $extensions[$ext] = $loaded;
        }

        $permissions = [];
        foreach ($required['permissions'] ?? [] as $path => $expected) {
            $full = base_path($path);
            $permissions[$path] = [
                'expected' => $expected,
                'exists' => file_exists($full),
                'writable' => is_writable($full),
            ];
        }

        $passes = $phpOk
            && ! in_array(false, $extensions, true)
            && collect($permissions)->every(fn ($p) => $p['exists'] && $p['writable']);

        return [
            'php' => ['required' => $phpRequired, 'current' => PHP_VERSION, 'ok' => $phpOk],
            'extensions' => $extensions,
            'permissions' => $permissions,
            'passes' => $passes,
        ];
    }
}
