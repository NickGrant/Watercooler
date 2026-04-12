<?php

declare(strict_types=1);

namespace Watercooler\Api\Config;

final class EnvFile
{
    public static function loadIfPresent(string $path): void
    {
        if (!is_file($path)) {
            return;
        }

        $lines = file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return;
        }

        foreach ($lines as $line) {
            $trimmed = trim($line);

            if ($trimmed === '' || str_starts_with($trimmed, '#')) {
                continue;
            }

            $separatorPosition = strpos($trimmed, '=');
            if ($separatorPosition === false) {
                continue;
            }

            $key = trim(substr($trimmed, 0, $separatorPosition));
            $value = trim(substr($trimmed, $separatorPosition + 1));

            if ($key === '') {
                continue;
            }

            $normalizedValue = self::stripWrappingQuotes($value);

            $_ENV[$key] ??= $normalizedValue;
            $_SERVER[$key] ??= $normalizedValue;

            if (getenv($key) === false) {
                putenv(sprintf('%s=%s', $key, $normalizedValue));
            }
        }
    }

    private static function stripWrappingQuotes(string $value): string
    {
        $length = strlen($value);
        if ($length < 2) {
            return $value;
        }

        $first = $value[0];
        $last = $value[$length - 1];

        if (($first === '"' && $last === '"') || ($first === "'" && $last === "'")) {
            return substr($value, 1, -1);
        }

        return $value;
    }
}
