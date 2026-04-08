<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

interface SlugGenerator
{
    public function generateCandidate(): string;
}
