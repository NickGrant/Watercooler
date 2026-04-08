<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Support;

final class ConsoleLogger
{
    public function __construct(
        private readonly bool $debugEnabled,
    ) {
    }

    public function info(string $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    public function debug(string $message, array $context = []): void
    {
        if (!$this->debugEnabled) {
            return;
        }

        $this->write('DEBUG', $message, $context);
    }

    private function write(string $level, string $message, array $context): void
    {
        $line = sprintf('[%s] %s', $level, $message);

        if ($context !== []) {
            $line .= ' ' . json_encode($context, JSON_UNESCAPED_SLASHES);
        }

        fwrite(STDOUT, $line . PHP_EOL);
    }
}
