<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

interface GameStateProjectionRepository
{
    public function loadState(int $gameId): ActiveGameState;
}
