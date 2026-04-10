<?php

declare(strict_types=1);

namespace Watercooler\Realtime\State;

use Watercooler\Realtime\Config\AppConfig;
use Watercooler\Realtime\Support\Logger;

final class ApiGameStateFetcher implements GameStateFetcher
{
    public function __construct(
        private readonly AppConfig $config,
        private readonly Logger $logger,
    ) {
    }

    public function fetch(string $slug, string $sessionToken): ?array
    {
        $url = sprintf(
            '%s/api/games/%s/state?sessionToken=%s',
            $this->config->apiBaseUrl,
            rawurlencode($slug),
            rawurlencode($sessionToken),
        );

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'ignore_errors' => true,
                'timeout' => 2.5,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        $headers = $http_response_header ?? [];
        $statusLine = $headers[0] ?? '';
        preg_match('/\s(\d{3})\s/', $statusLine, $matches);
        $statusCode = isset($matches[1]) ? (int) $matches[1] : 0;

        if ($body === false || $statusCode >= 400) {
            $this->logger->debug('Realtime state fetch missed.', [
                'slug' => $slug,
                'statusCode' => $statusCode,
            ]);

            return null;
        }

        $decoded = json_decode($body, true);

        if (!is_array($decoded)) {
            $this->logger->debug('Realtime state fetch returned invalid JSON.', [
                'slug' => $slug,
            ]);

            return null;
        }

        return $decoded;
    }
}
