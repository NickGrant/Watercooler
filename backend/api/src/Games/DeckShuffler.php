<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

interface DeckShuffler
{
    /**
     * @template T
     *
     * @param list<T> $items
     * @return list<T>
     */
    public function shuffle(array $items): array;
}
