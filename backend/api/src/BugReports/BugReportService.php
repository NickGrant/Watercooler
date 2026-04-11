<?php

declare(strict_types=1);

namespace Watercooler\Api\BugReports;

use Watercooler\Api\Games\GameRepository;

final class BugReportService
{
    public function __construct(
        private readonly GameRepository $gameRepository,
        private readonly BugReportContextRepository $contextRepository,
        private readonly BugReportRepository $bugReportRepository,
    ) {
    }

    /**
     * @param array<string, mixed>|null $payload
     */
    public function submit(string $slug, ?array $payload, ?string $userAgent): BugReportResult
    {
        $game = $this->gameRepository->findBySlug($slug);
        if ($game === null) {
            throw new BugReportException(404, 'game_not_found', 'No game exists for the provided slug.');
        }

        $sessionToken = trim((string) ($payload['sessionToken'] ?? ''));
        if ($sessionToken === '') {
            throw new BugReportException(
                401,
                'session_token_required',
                'A valid temporary session token is required to submit a bug report.',
            );
        }

        $context = $this->contextRepository->findReporterContext($game->id, hash('sha256', $sessionToken));
        if ($context === null) {
            throw new BugReportException(
                401,
                'invalid_session_token',
                'The provided temporary session token is invalid for this game.',
            );
        }

        $message = trim((string) ($payload['message'] ?? ''));
        if ($message === '') {
            throw new BugReportException(
                422,
                'message_required',
                'A short description of the bug is required before submitting a report.',
            );
        }

        $replyEmail = trim((string) ($payload['replyEmail'] ?? ''));
        if ($replyEmail !== '' && filter_var($replyEmail, FILTER_VALIDATE_EMAIL) === false) {
            throw new BugReportException(
                422,
                'invalid_reply_email',
                'Reply email must be a valid email address when provided.',
            );
        }

        $receipt = $this->bugReportRepository->create(
            new BugReportSubmission(
                gameId: $game->id,
                roomSlug: $game->slug,
                reporterGamePlayerId: $context->reporterGamePlayerId,
                reporterDisplayName: $context->reporterDisplayName,
                reporterSeatOrder: $context->reporterSeatOrder,
                replyEmail: $replyEmail === '' ? null : $replyEmail,
                message: $message,
                gameStatusSnapshot: $game->status,
                gamePhaseSnapshot: $game->phase,
                currentTurnGamePlayerId: $context->currentTurnGamePlayerId,
                clientUserAgent: $this->normalizeUserAgent($userAgent),
            ),
        );

        return new BugReportResult($receipt);
    }

    private function normalizeUserAgent(?string $userAgent): ?string
    {
        $trimmed = trim((string) $userAgent);

        if ($trimmed === '') {
            return null;
        }

        return mb_substr($trimmed, 0, 512);
    }
}
