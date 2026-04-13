<?php

declare(strict_types=1);

namespace Watercooler\Api\Players;

final class AvatarSelection
{
    public function __construct(
        // BEGIN AGENT CHANGE
        public readonly string $id,
        // END AGENT CHANGE
    ) {
    }

    /**
     * @return array{id: string}
     */
    public function toArray(): array
    {
        return [
            // BEGIN AGENT CHANGE
            'id' => $this->id,
            // END AGENT CHANGE
        ];
    }
}
