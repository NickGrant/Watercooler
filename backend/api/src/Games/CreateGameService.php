<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class CreateGameService
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly SlugGenerator $slugGenerator,
    ) {
    }

    public function createGame(): GameSummary
    {
        $baseSlug = '';

        for ($attempt = 1; $attempt <= 20; $attempt++) {
            $baseSlug = $this->slugGenerator->generateCandidate();
            $slug = $attempt <= 10 ? $baseSlug : sprintf('%s-%d', $baseSlug, $attempt + 31);

            if ($this->gameRepository->slugExists($slug)) {
                continue;
            }

            return $this->gameRepository->createGame($slug);
        }

        throw new \RuntimeException('Unable to generate a unique game slug after repeated attempts.');
    }
}
