<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class PlayerResourceSet
{
    public function __construct(
        public readonly int $coffee,
        public readonly int $spreadsheets,
        public readonly int $budget,
        public readonly int $connections,
        public readonly int $time,
        public readonly int $executiveFavor,
    ) {
    }

    public function totalTokens(): int
    {
        return $this->coffee
            + $this->spreadsheets
            + $this->budget
            + $this->connections
            + $this->time
            + $this->executiveFavor;
    }

    /**
     * @return array<string, int>
     */
    public function toArray(): array
    {
        return [
            'coffee' => $this->coffee,
            'spreadsheets' => $this->spreadsheets,
            'budget' => $this->budget,
            'connections' => $this->connections,
            'time' => $this->time,
            'executiveFavor' => $this->executiveFavor,
            'totalTokens' => $this->totalTokens(),
        ];
    }
}
