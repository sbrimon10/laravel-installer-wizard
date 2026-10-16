<?php

namespace Sbrimon10\InstallerWizard\Support;

class EnvManager
{
    public function path(): string
    {
        return base_path('.env');
    }

    /**
     * Read current values for the given keys from the .env file.
     * Falls back to env()/config when the file or key is missing.
     *
     * @param  array<int,string>  $keys
     * @return array<string, string|null>
     */
    public function get(array $keys): array
    {
        $parsed = $this->parse();
        $out = [];

        foreach ($keys as $key) {
            $out[$key] = $parsed[$key] ?? env($key);
        }

        return $out;
    }

    /**
     * Update or append keys in the .env file. Preserves comments/order,
     * groups fresh keys with their family, drops duplicates.
     * Skips the write entirely when nothing would change (keeps the
     * file mtime stable so dev servers don't restart pointlessly).
     *
     * @param  array<string, mixed>  $data
     * @return bool true when the file was written
     */
    public function update(array $data): bool
    {
        $path = $this->path();

        if (! file_exists($path)) {
            file_put_contents($path, '');
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES) ?? [];

        if (! $this->isDirty($lines, $data)) {
            return false;
        }

        $seen = [];

        foreach ($lines as $i => $line) {
            $trimmed = ltrim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $pos));

            if (array_key_exists($key, $data)) {
                if (isset($seen[$key])) {
                    unset($lines[$i]); // drop pre-existing duplicates
                    continue;
                }
                $lines[$i] = $key.'='.$this->formatValue($data[$key]);
                $seen[$key] = true;
            }
        }

        foreach ($data as $key => $value) {
            if (isset($seen[$key])) {
                continue;
            }

            $line = $key.'='.$this->formatValue($value);
            $anchor = $this->groupAnchor($key, $lines);

            if ($anchor === null) {
                $lines[] = $line;
            } else {
                array_splice($lines, $anchor + 1, 0, [$line]);
            }

            $seen[$key] = true;
        }

        file_put_contents($path, implode(PHP_EOL, array_values($lines)).PHP_EOL);

        return true;
    }

    /**
     * True when writing would change something (new/missing key,
     * different value, or duplicate lines to collapse).
     */
    protected function isDirty(array $lines, array $data): bool
    {
        $found = [];

        foreach ($lines as $line) {
            $trimmed = ltrim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            $pos = strpos($line, '=');

            if ($pos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $pos));

            if (! array_key_exists($key, $data)) {
                continue;
            }

            $found[$key] = ($found[$key] ?? 0) + 1;

            if ($found[$key] > 1) {
                return true; // duplicates to collapse
            }

            if (trim(substr($line, $pos + 1)) !== $this->formatValue($data[$key])) {
                return true;
            }
        }

        foreach ($data as $key => $value) {
            if (! isset($found[$key])) {
                return true; // missing key to append
            }
        }

        return false;
    }

    /**
     * Index of the last line belonging to the key's group (DB_*, APP_*…),
     * so fresh keys land next to their family instead of file bottom.
     */
    protected function groupAnchor(string $key, array $lines): ?int
    {
        $pos = strpos($key, '_');

        if ($pos === false) {
            return null;
        }

        $prefix = substr($key, 0, $pos + 1);
        $anchor = null;

        foreach ($lines as $i => $line) {
            $trimmed = ltrim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            $eq = strpos($line, '=');

            if ($eq === false) {
                continue;
            }

            if (str_starts_with(trim(substr($line, 0, $eq)), $prefix)) {
                $anchor = $i;
            }
        }

        return $anchor;
    }

    /**
     * @return array<string, string>
     */
    protected function parse(): array
    {
        $path = $this->path();

        if (! file_exists($path)) {
            return [];
        }

        $out = [];
        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?? [];

        foreach ($lines as $line) {
            $trimmed = ltrim($line);

            if (str_starts_with($trimmed, '#')) {
                continue;
            }

            $pos = strpos($line, '=');
            if ($pos === false) {
                continue;
            }

            $key = trim(substr($line, 0, $pos));
            $value = trim(substr($line, $pos + 1));

            // Strip surrounding quotes.
            if (strlen($value) >= 2 && (
                ($value[0] === '"' && $value[-1] === '"') ||
                ($value[0] === "'" && $value[-1] === "'")
            )) {
                $value = substr($value, 1, -1);
            }

            $out[$key] = $value;
        }

        return $out;
    }

    protected function formatValue(mixed $value): string
    {
        $value = (string) ($value ?? '');

        if ($value === '' || preg_match('/[\s#\'"]/', $value)) {
            return '"'.str_replace('"', '\"', $value).'"';
        }

        return $value;
    }
}
