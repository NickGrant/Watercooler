<?php

declare(strict_types=1);

namespace Watercooler\Api\Games;

final class OfficeSlugGenerator implements SlugGenerator
{
    private const CATEGORY_A = [
        'synergy',
        'forward',
        'strategic',
        'dynamic',
        'agile',
        'scalable',
        'optimized',
        'integrated',
        'proactive',
        'aligned',
        'executive',
        'premium',
        'streamlined',
        'value',
        'crossfunctional',
    ];

    private const CATEGORY_B = [
        'report',
        'meeting',
        'budget',
        'initiative',
        'roadmap',
        'calendar',
        'workflow',
        'dashboard',
        'pipeline',
        'memo',
        'review',
        'briefing',
        'alignment',
        'deliverable',
        'projection',
    ];

    private const CATEGORY_C = [
        'logistics',
        'telemetry',
        'operations',
        'enablement',
        'compliance',
        'analytics',
        'visibility',
        'transformation',
        'strategy',
        'infrastructure',
        'efficiency',
        'governance',
        'optimization',
        'engagement',
        'execution',
    ];

    public function generateCandidate(): string
    {
        return implode('-', [
            self::CATEGORY_A[array_rand(self::CATEGORY_A)],
            self::CATEGORY_B[array_rand(self::CATEGORY_B)],
            self::CATEGORY_C[array_rand(self::CATEGORY_C)],
        ]);
    }
}
