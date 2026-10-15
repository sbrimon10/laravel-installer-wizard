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
     * Update or append keys in the .env file. Preserves comments/order.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(array $data): void
    {
        $path = $this->path();

        if (! file_exists($path)) {
            file_put_contents($path, '');
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES) ?? [];
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
                $lines[$i] = $key.'='.$this->formatValue($data[$key]);
                $seen[$key] = true;
            }
        }

        foreach ($data as $key => $value) {
            if (! isset($seen[$key])) {
                $lines[] = $key.'='.$this->formatValue($value);
            }
        }

        file_put_contents($path, implode(PHP_EOL, $lines).PHP_EOL);
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
