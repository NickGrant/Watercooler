<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class RandomDeckShuffler implements DeckShuffler
{
    public function shuffle(array $items): array
    {
        $shuffled = array_values($items);
        $count = count($shuffled);

        for ($index = $count - 1; $index > 0; $index--) {
            $swapIndex = random_int(0, $index);
            [$shuffled[$index], $shuffled[$swapIndex]] = [$shuffled[$swapIndex], $shuffled[$index]];
        }

        return $shuffled;
    }
}
