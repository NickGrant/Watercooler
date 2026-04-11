# Architecture

## System Shape

Watercooler currently operates as a three-part system for the default deployment path:

1. Angular frontend
2. PHP HTTP API
3. MySQL database

Optional future work may reintroduce a separate push-transport service, but that is not part of the current shared-hosting-first architecture.

## Core Architectural Principles

- server-authoritative gameplay
- session-based multiplayer, not accounts
- modular separation between rules, transport, persistence, and theme content
- enough persistence to recover active or completed games
- documentation and code organized so future agents can load only relevant context

## Responsibility Split

### Frontend

- render screens and game state
- collect player intent
- manage local join/session state
- refresh authoritative state through smart polling after successful join
- present legal actions clearly without acting as final authority

### HTTP API

- create games
- generate and validate unique slugs
- return game bootstrap data
- validate join prerequisites
- handle authoritative gameplay actions
- expose polling-friendly state reads and recovery endpoints

### Database

- store game metadata, players, avatars, state snapshots or normalized game state
- preserve enough data to reconstruct active or completed games
- support slug uniqueness and join/session validation

## Boundaries To Preserve

- gameplay rules should be reusable apart from UI theme text
- content names and humorous copy should not be hard-wired into validation logic
- synchronization transport should not be tightly coupled to UI components
- persistence shape should support recovery without relying on in-memory room state alone

## Suggested Codebase Areas For Future Work

- `frontend/`: Angular app
- `backend/api/`: PHP HTTP API
- `backend/shared/`: shared rules and domain logic if a clean PHP boundary emerges
- `database/`: migrations, seeds, and schema docs
- `resources/planning/`: living design docs

See `project-structure.md` for the concrete repository layout and boundary decisions used during scaffolding.

## Main Risks

- mixing game rules into transport handlers
- letting the client drift into authority on move legality
- over-coupling thematic copy with the mechanics layer
- under-specifying persistence for reconnect and recovery
