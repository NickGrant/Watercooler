<?php

declare(strict_types=1);

namespace Watercooler\Realtime\State;

interface GameStateFetcher
{
    /**
     * @return array<string, mixed>|null
     */
    public function fetch(string $slug, string $sessionToken): ?array;
}
