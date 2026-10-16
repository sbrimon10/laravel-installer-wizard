<?php

namespace Sbrimon10\InstallerWizard\Support;

class RequirementsChecker
{
    /**
     * @return array{php: array, required: array, optional: array, permissions: array, loaded: array, passes: bool}
     */
    public function check(): array
    {
        $required = config('installer-wizard.requirements', []);

        $phpRequired = $required['php'] ?? '8.3.0';
        $phpOk = version_compare(PHP_VERSION, $phpRequired, '>=');

        [$requiredExts, $optionalExts] = $this->normalizeExtensions($required['extensions'] ?? []);

        $requiredMap = [];
        foreach ($requiredExts as $ext) {
            $requiredMap[$ext] = extension_loaded($ext);
        }

        $optionalMap = [];
        foreach ($optionalExts as $ext) {
            $optionalMap[$ext] = extension_loaded($ext);
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

        // Optional extensions NEVER block.
        $passes = $phpOk
            && ! in_array(false, $requiredMap, true)
            && collect($permissions)->every(fn ($p) => $p['exists'] && $p['writable']);

        $loaded = get_loaded_extensions();
        natcasesort($loaded);

        return [
            'php' => ['required' => $phpRequired, 'current' => PHP_VERSION, 'ok' => $phpOk],
            'required' => $requiredMap,
            'optional' => $optionalMap,
            // Back-compat alias: required list in the old flat shape.
            'extensions' => $requiredMap,
            'permissions' => $permissions,
            'loaded' => array_values($loaded),
            'passes' => $passes,
        ];
    }

    /**
     * Accepts the new ['required'=>[], 'optional'=>[]] shape and the
     * legacy flat list (treated as all-required for old published configs).
     *
     * @return array{0: array, 1: array}
     */
    protected function normalizeExtensions(mixed $extensions): array
    {
        if (! is_array($extensions)) {
            return [[], []];
        }

        if (array_key_exists('required', $extensions) || array_key_exists('optional', $extensions)) {
            return [
                array_values((array) ($extensions['required'] ?? [])),
                array_values((array) ($extensions['optional'] ?? [])),
            ];
        }

        return [array_values($extensions), []];
    }
}
