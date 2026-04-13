<?php

declare(strict_types=1);

namespace Watercooler\Api\Players;

final class AvatarCatalog
{
    public function isValid(AvatarSelection $selection): bool
    {
        // BEGIN AGENT CHANGE
        return preg_match('/^avatar-(?:[1-9]|1[0-5])$/', $selection->id) === 1;
        // END AGENT CHANGE
    }
}
