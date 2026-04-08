# Backend API Plan

## Purpose

The PHP HTTP API handles game creation, lookup, join preparation, and non-realtime bootstrap behavior.

## Main Responsibilities

- create a new game record
- generate unique office-jargon slugs
- fetch game metadata by slug
- validate join attempts before realtime connection
- return bootstrap data needed by the frontend

## Suggested Endpoints

- `POST /api/games`
- `GET /api/games/{slug}`
- `POST /api/games/{slug}/join-bootstrap`
- `GET /api/games/{slug}/state`

Additional endpoints are acceptable if they keep responsibilities clear and avoid turning the HTTP API into a duplicate realtime transport.

## Join-Bootstrap Expectations

The join/bootstrap path should:

- validate trimmed display name presence
- enforce game-scoped name uniqueness
- validate avatar selections
- reject invalid game states such as full or already-finished games
- create or return a temporary session token if reconnect support is enabled
- return enough information for the client to open the WebSocket connection safely

## Slug Requirements

- three jargon words joined by hyphens
- lowercase only
- unique across games
- regenerated on collision
- optional short numeric suffix only after repeated collisions

## API Risks

- letting the frontend infer state the API should return directly
- mixing long-running realtime responsibilities into request-response handlers
- failing to return enough bootstrap data for reconnect and lobby rendering
