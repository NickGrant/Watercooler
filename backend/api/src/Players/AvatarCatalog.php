<?php

declare(strict_types=1);

namespace Watercooler\Api\Players;

final class AvatarCatalog
{
    private const BODY_OPTIONS = [
        'blazer',
        'hoodie',
        'cardigan',
        'polo',
        'power-suit',
    ];

    private const FACE_OPTIONS = [
        'corporate-neutral',
        'coffee-grin',
        'meeting-fatigue',
        'deadline-focus',
        'visionary-smirk',
    ];

    private const HAIR_OPTIONS = [
        'side-part',
        'executive-swoop',
        'startup-mess',
        'weekend-buzz',
        'presentation-curl',
    ];

    public function isValid(AvatarSelection $selection): bool
    {
        return in_array($selection->body, self::BODY_OPTIONS, true)
            && in_array($selection->face, self::FACE_OPTIONS, true)
            && in_array($selection->hair, self::HAIR_OPTIONS, true);
    }
}
