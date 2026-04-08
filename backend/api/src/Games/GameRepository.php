<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

interface GameRepository
{
    public function slugExists(string $slug): bool;

    public function createGame(string $slug): GameSummary;

    public function findBySlug(string $slug): ?GameSummary;
}
