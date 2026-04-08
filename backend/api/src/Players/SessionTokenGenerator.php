<?php

declare(strict_types=1);

namespace Watercooler\Api\Players;

interface SessionTokenGenerator
{
    public function generate(): string;
}
