<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Support;

interface Logger
{
    public function info(string $message, array $context = []): void;

    public function debug(string $message, array $context = []): void;
}
