<?php

declare(strict_types=1);

namespace Watercooler\Realtime\Tests\Server;

use PHPUnit\Framework\TestCase;
use Watercooler\Realtime\Config\AppConfig;
use Watercooler\Realtime\Rooms\ActiveRoomRegistry;
use Watercooler\Realtime\Server\RealtimeServer;
use Watercooler\Realtime\Tests\Support\InMemoryLogger;

final class RealtimeServerTest extends TestCase
{
    public function testRunOnceLogsBootstrapPreviewAndPopulatesTheRegistry(): void
    {
        $logger = new InMemoryLogger();
        $registry = new ActiveRoomRegistry();
        $server = new RealtimeServer(
            new AppConfig('development', true, '0.0.0.0', 8090),
            $logger,
            $registry,
        );

        $server->run(true);

        self::assertCount(3, $logger->entries);
        self::assertSame('Realtime service scaffold booted.', $logger->entries[0]['message']);
        self::assertArrayHasKey('synergy-report-telemetry', $registry->snapshot());
    }
}
